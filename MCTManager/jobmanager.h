#ifndef JOBMANAGER_H
#define JOBMANAGER_H
#include <QObject>
#include <QJsonDocument>

#include <mongocxx/client.hpp>
#include <mongocxx/uri.hpp>


class JobManager : public QObject
{
    Q_OBJECT
public:
    explicit JobManager(QObject *parent = 0);
private:
    mongocxx::client *m_conn = 0;
    mongocxx::database m_db;
signals:
    void finishedJob(qint64 delivery_tag);
public slots:
    void on_newJob(QJsonDocument json, qint64 delivery_tag);
    void on_finished(qint64 delivery_tag);
    void on_finished(QJsonDocument json);
};
    

#endif // JOBMANAGER_H
