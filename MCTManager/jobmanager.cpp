#include "jobmanager.h"

#include <QDebug>
#include <QThread>
#include <QJsonObject>
#include <QSettings>
#include "job.h"

#include <bsoncxx/builder/stream/document.hpp>
#include <bsoncxx/types.hpp>
#include <bsoncxx/json.hpp>
#include <bsoncxx/string/view_or_value.hpp>

using bsoncxx::builder::stream::document;
using bsoncxx::builder::stream::open_document;
using bsoncxx::builder::stream::close_document;
using bsoncxx::builder::stream::open_array;
using bsoncxx::builder::stream::close_array;
using bsoncxx::builder::stream::finalize;


JobManager::JobManager(QObject *parent) : QObject(parent)
{
    QSettings s;
    bsoncxx::string::view_or_value conn_uri(s.value("mongodb_uri").toString().toStdString());
    m_conn = new mongocxx::client{mongocxx::uri{conn_uri}};
    if(m_conn) m_db = m_conn->database("mct_prod");
    qDebug() << "created";
}

void JobManager::on_newJob(QJsonDocument json, qint64 delivery_tag)
{
    qDebug();
    qDebug() << "delivery_tag" << delivery_tag;
    QThread *thread = new QThread(this);

    Job *job = new Job(json, delivery_tag);
    job->moveToThread(thread);

    connect(thread, SIGNAL(started()), job, SLOT(process()));

    connect(job, SIGNAL(finished(qint64)), this, SLOT(on_finished(qint64)));
    connect(job, SIGNAL(finished(QJsonDocument)), this, SLOT(on_finished(QJsonDocument)));

    connect(job, SIGNAL(finishedThread()), thread, SLOT(quit()));
    connect(job, SIGNAL(finishedThread()), job, SLOT(deleteLater()));
    connect(thread, SIGNAL(finished()), thread, SLOT(deleteLater()));

    thread->start();
}

void JobManager::on_finished(qint64 delivery_tag)
{
    emit finishedJob(delivery_tag);
}

void JobManager::on_finished(QJsonDocument json)
{
    qDebug() << "Writing job to db";
    bsoncxx::document::value job_bson = bsoncxx::from_json(json.toJson().toStdString());

    auto res = m_db["jobs"].insert_one(job_bson.view());
}

