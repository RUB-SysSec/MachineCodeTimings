#include "job.h"
#include <QDebug>
#include <QTimer>
#include <QJsonObject>
#include <QJsonArray>
#include <QSqlQuery>
#include <QEventLoop>
#include <QSettings>


Job::Job(QJsonDocument json, qint64 delivery_tag, QObject *parent) : QObject(parent),
    m_json(json),
    m_delivery_tag(delivery_tag)
{
    qDebug() << "created";
    // Do NOT create any objects on the heap in the constructor!
    connect(this, SIGNAL(allExchangesRdy()), this, SLOT(on_allExchangesRdy()));
    connect(this, SIGNAL(allExchangesRdy()), this, SLOT(connectToSqlDatabase()));
    connect(this, SIGNAL(connectedToSqlDatabase()), this, SLOT(on_connectedToSqlDatabase()));

    m_loopSize = m_json.object()["loop_size"].toInt();

}

void Job::process()
{
    qDebug();
    // Create Channel for nodes if they do not exsist already
    foreach(QJsonValue node, m_json.object()["nodes"].toArray()){
        AmqpExchange *exchange = new AmqpExchange("node_"+node.toString(), true, 1, this);
        m_exchangeMap[node.toString()] = exchange;
        m_exchangeRdyMap[exchange] = false;
        connect(exchange, SIGNAL(exchangeReady()), this, SLOT(on_exchangeReady()));
    }
}

void Job::done()
{
    qDebug();

    emit finished(m_delivery_tag);
    emit finished(m_json);

    m_db.close();
    m_db = QSqlDatabase(); // http://stackoverflow.com/questions/9519736/warning-remove-databases
    QSqlDatabase::removeDatabase(m_db.connectionName());

    // Hacky! :( Sometimes not all messages do get queued, if we quit this thread immediately
    QEventLoop loop;
    QTimer::singleShot(2000, &loop, SLOT(quit()));
    loop.exec();

    emit finishedThread();
}


Job::~Job()
{

    qDebug();
}

QString Job::intrinsicTemplate() const
{
    return m_intrinsicTemplate;
}

int Job::loopSize() const
{
    return m_loopSize;
}

QMap<int, QString> Job::templateMap() const
{
    return m_templateMap;
}


void Job::on_exchangeReady()
{
    AmqpExchange *exchange = qobject_cast<AmqpExchange*>(QObject::sender());
    m_exchangeRdyMap[exchange] = true;
    bool allrdy = false;
    foreach(bool rdy, m_exchangeRdyMap){
        if(rdy == false){
            allrdy = false;
            break;
        }
        else allrdy = true;
    }
    if(allrdy) emit allExchangesRdy();
}

void Job::on_allExchangesRdy()
{
    qDebug();
}

void Job::connectToSqlDatabase()
{
    qDebug();
    QSettings s;

    m_db = QSqlDatabase::addDatabase("QMYSQL");
    m_db.setHostName(s.value("sql_host").toString());
    m_db.setDatabaseName(s.value("sql_db").toString());
    m_db.setUserName(s.value("sql_user").toString());
    m_db.setPassword(s.value("sql_pw").toString());

    if(m_db.open()){
        emit connectedToSqlDatabase();
    }
    else qDebug() << "Could not connect to database";
}

void Job::on_connectedToSqlDatabase()
{
    qDebug() << "Connected to Database";
    // Getting template
    QSqlQuery template_query("SELECT * FROM templates");
    if(template_query.size() > 0){
        while(template_query.next()){
            m_templateMap[template_query.value("id").toInt()] = template_query.value("template").toString();
        }
    }
    // Todo
    QJsonArray intrinsics = m_json.object()["instruction_ids"].toArray();
    foreach(QJsonValue intrinsic_id, intrinsics){
        Intrinsic *intrinsic = new Intrinsic(intrinsic_id.toInt(), m_json.object()["force_parameterTypeInitCategory"].toInt(), this); // TODO

        connect(intrinsic, SIGNAL(codeGenFinished()), this, SLOT(on_codeGenFinished()));
        connect(intrinsic, SIGNAL(templateFinished(QString, QVariantMap)), this, SLOT(on_templateFinished(QString, QVariantMap)));

        /*if(intrinsic->valid()){
            m_intrinsicList.append(intrinsic);
        }
        else intrinsic->deleteLater();*/
        m_intrinsicList.append(intrinsic);
    }
    nextIntrinsic();
    //foreach(Intrinsic* intrinsic, m_intrinsicList) intrinsic->buildMeasurementPrograms();

}

void Job::nextIntrinsic()
{
    Intrinsic *intrinsic = m_intrinsicList.takeFirst();
    intrinsic->build();
    if(intrinsic->valid()){
        intrinsic->buildMeasurementPrograms();
    }
}

void Job::on_codeGenFinished()
{
    Intrinsic* intrinsic = qobject_cast<Intrinsic*>(QObject::sender());
    //m_intrinsicList.removeOne(intrinsic);
    intrinsic->deleteLater();

    if(m_intrinsicList.isEmpty()){
        qDebug() << "Intrinsic List is empty";

        done();
    }
    else nextIntrinsic();
}

void Job::on_templateFinished(QString filledTemplate, QVariantMap usedParameters)
{
    QJsonDocument jsonDoc;
    QVariantMap map;
    Intrinsic *intrinsic = qobject_cast<Intrinsic*>(QObject::sender());
    // TODO
    map["command"] = filledTemplate.toLatin1().toBase64();
    map["repetitions"] = m_json.object()["repetitions"].toInt();
    map["job_id"] = m_json.object()["id"].toString();
    if(!m_json.object()["force_compiler_command"].toBool()){

        // Use compiler command of instruction or use supplied one if instruction has none set.
        if(intrinsic->compilerCommand().isEmpty()){

            // Use supplied one, because the instruction has none set
            map["compiler"] = m_json.object()["compiler"].toString();
            map["compiler_options"] = m_json.object()["compiler_options"].toString();
        }
        else{
            // Use the one if the instruction
            QString command = intrinsic->compilerCommand();
            map["compiler"] = command.section(" ", 0, 0);
            map["compiler_options"] = command.section(" ", 1).trimmed();
        }
    }
    else{

        // Force supplied compiler command
        map["compiler"] = m_json.object()["compiler"].toString();
        map["compiler_options"] = m_json.object()["compiler_options"].toString();

    }
    map["intrinsic_id"] = qobject_cast<Intrinsic*>(QObject::sender())->id();
    map["parameters"] = usedParameters; // Used paramater initializers
    jsonDoc.setObject(QJsonObject::fromVariantMap(map));

    foreach(AmqpExchange *exchange, m_exchangeMap){

        exchange->writeMessage(jsonDoc.toJson());
    }
}
