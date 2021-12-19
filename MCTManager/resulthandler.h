#ifndef RESULTHANDLER_H
#define RESULTHANDLER_H

#include <QObject>
#include <QJsonDocument>

#include <mongocxx/client.hpp>
#include <mongocxx/uri.hpp>
#include <mongocxx/instance.hpp>


class ResultHandler : public QObject
{
    Q_OBJECT
public:
    explicit ResultHandler(QObject *parent = 0);

private:

    mongocxx::client *m_conn;
    mongocxx::database m_db;

signals:
    void finishedResult(qint64 delivery_tag);
    void finishedError(qint64 delivery_tag);

public slots:
    void on_newResult(QJsonDocument result, qint64 delivery_tag);
    void on_newError(QJsonDocument error, qint64 delivery_tag);
};

#endif // RESULTHANDLER_H
