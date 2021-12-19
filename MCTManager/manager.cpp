#include "manager.h"

#include <QCoreApplication>
#include <QJsonDocument>
#include <signal.h>
#include <QSettings>

Manager::Manager(QObject *parent) : QObject(parent)
{

    connect(QCoreApplication::instance(), SIGNAL(aboutToQuit()), this, SLOT(on_aboutToQuit()));

    setDefaultSettings();

    m_resultHandler = new ResultHandler(this);
    m_jobManager = new JobManager(this);

    AmqpClient* resultsAmqpClient = new AmqpClient("results", true, 1, true, this);
    connect(resultsAmqpClient, SIGNAL(newMessage(QJsonDocument, qint64)), m_resultHandler, SLOT(on_newResult(QJsonDocument, qint64)));
    connect(m_resultHandler, SIGNAL(finishedResult(qint64)), resultsAmqpClient, SLOT(ackMessage(qint64)));

    AmqpClient* errorAmqpClient = new AmqpClient("errors", true, 1, true, this);
    connect(errorAmqpClient, SIGNAL(newMessage(QJsonDocument, qint64)), m_resultHandler, SLOT(on_newError(QJsonDocument, qint64)));
    connect(m_resultHandler, SIGNAL(finishedError(qint64)), errorAmqpClient, SLOT(ackMessage(qint64)));


    AmqpClient* jobsAmqpClient = new AmqpClient("jobs", true, 1, true, this);
    connect(jobsAmqpClient, SIGNAL(newMessage(QJsonDocument,qint64)), m_jobManager, SLOT(on_newJob(QJsonDocument, qint64)));
    connect(m_jobManager, SIGNAL(finishedJob(qint64)), jobsAmqpClient, SLOT(ackMessage(qint64)));
}

Manager::~Manager()
{
    qDebug() << "quitting";

}

void Manager::setDefaultSettings()
{
    QSettings s;
    if(!s.value("default_settings_set").toBool()){

        s.setValue("mongodb_host", "localhost");
        s.setValue("mongodb_port", 27017);
        s.setValue("mongodb_user", "");
        s.setValue("mongodb_pw", "");
        s.setValue("mongodb_uri", "mongodb://localhost:27017");

        s.setValue("mongodb_db", "mct_prod");

        s.setValue("sql_host", "localhost");
        s.setValue("sql_user", "mct");
        s.setValue("sql_pw", "");
        s.setValue("sql_db", "mct_prod");

        s.setValue("mq_host", "127.0.0.1");
        s.setValue("mq_user", "manager");
        s.setValue("mq_pw", "");

        s.setValue("default_settings_set", true);
    }

}

void Manager::on_aboutToQuit()
{
    qDebug() << this << "quitting. Cleaning up...";

}

void Manager::on_unixSignal(int signal)
{
    if(signal == SIGINT) QCoreApplication::instance()->quit();
    if(signal == SIGQUIT) QCoreApplication::instance()->quit();
    if(signal == SIGTERM) QCoreApplication::instance()->quit();
}
