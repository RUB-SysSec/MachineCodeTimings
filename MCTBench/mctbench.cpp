#include "mctbench.h"
#include <QDebug>
#include <QSettings>
#include <QFile>
#include <QNetworkReply>
#include <QJsonDocument>
#include <QJsonObject>
#include <QJsonArray>
#include <QTimer>
#include <iostream>
#include <QCoreApplication>
#include <QUuid>
#include <benchmark/gccbenchmark.h>
#include <benchmark/msvcbenchmark.h>

MCTBench::MCTBench(QObject *parent) : QObject(parent)
{
    qStdout() << " __  __  ___ _____ ___              _    \n"
                 "|  \\/  |/ __|_   _| _ ) ___ _ _  __| |_  \n"
                 "| |\\/| | (__  | | | _ \\/ -_) ' \\/ _| ' \\ \n"
                 "|_|  |_|\\___| |_| |___/\\___|_||_\\__|_||_|\n\n";
    qStdout().flush();
    QSettings s;
    qDebug() << "Settings file is" << s.fileName();
    if(!s.isWritable()) qWarning() << "Cannot save settings";
    if(s.value("uuid", "").toString().isEmpty()) s.setValue("uuid", QUuid::createUuid().toString());
    qDebug() << "uuid:" << s.value("id").toString();

    // Networkacceess
    m_nam = new QNetworkAccessManager(this);

    m_cpu = new CPU(this);

    questionUser();

#if defined(Q_OS_WIN)
    m_benchmark = new MSVCBenchmark(this);
#elif defined(Q_OS_LINUX)
    m_benchmark = new GCCBenchmark(this);
#else
    m_benchmark = new Benchmark(this);
#endif

    connect(m_benchmark, &Benchmark::finished, this, &MCTBench::on_benchmarkFinished);
    connect(m_benchmark, &Benchmark::finished, this, &MCTBench::submitResults);
    m_benchmark->startBenchmark();

}

MCTBench::~MCTBench()
{
    qDebug();
}

void MCTBench::questionUser()
{
    qDebug();
    QSettings s;
    QString input;

    // Name / Contact
    qStdout() << "Your name/contact [" << s.value("name", "").toString() << "]: ";
    qStdout().flush();
    input = QTextStream(stdin).readLine();
    if(!input.isEmpty()) s.setValue("name", input);

    // CPU
    qStdout() << "Your CPU [" << s.value("cpu", m_cpu->processorName()).toString() << "]: ";
    qStdout().flush();
    input = QTextStream(stdin).readLine();
    if(!input.isEmpty()) s.setValue("cpu", input);

}

void MCTBench::submitResults()
{
    qDebug();
    QSettings s;

    // Convert QVariantMap to JsonArray
    QJsonArray jsonArray;
    foreach(QVariantMap map, m_benchmark->results()){
        jsonArray.append(QJsonObject::fromVariantMap(map));
    }

    // Build final JsonDoc
    QJsonObject jsonObject;
    jsonObject["cpu"] = s.value("cpu").toString();
    jsonObject["raw_cpu"] = m_cpu->processorName();
    jsonObject["raw_vendor"] = m_cpu->vendor();
    jsonObject["name"] = s.value("name").toString();
    jsonObject["uuid"] = s.value("uuid").toString();
    jsonObject["results"] = jsonArray;
    QJsonDocument jsonDoc;
    jsonDoc.setObject(jsonObject);
    QByteArray jsonToSend = jsonDoc.toJson();

    qStdout() << QString(jsonDoc.toJson(QJsonDocument::Indented));
    qStdout().flush();

    //QCoreApplication::instance()->quit(); // Does not work
    QTimer::singleShot(1000, QCoreApplication::instance(), SLOT(quit())); // Does work. Why?

    // Networkstuff
/*
    QNetworkRequest request = QNetworkRequest(QUrl("http://192.168.56.102/mctbench/submit"));
    request.setRawHeader("Content-Type", "application/json");
    request.setRawHeader("Content-Length", QByteArray::number(jsonToSend.size()));

    QNetworkReply *reply = m_nam->post(request, jsonToSend);
    connect(reply, &QNetworkReply::readyRead, [=](){

        QByteArray replyContent = reply->readAll();
        QJsonDocument json = QJsonDocument::fromJson(replyContent);
        if(json.object()["success"].toBool()) qDebug() << "success";
        else qDebug() << "unsuccessful";

        reply->deleteLater();;

        QCoreApplication::instance()->quit();
    });
    */

}

void MCTBench::on_aboutToQuit()
{
    qDebug();
}

void MCTBench::on_benchmarkFinished()
{
    qDebug();
}


