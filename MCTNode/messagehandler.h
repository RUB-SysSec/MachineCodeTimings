#ifndef MESSAGEHANDLER_H
#define MESSAGEHANDLER_H

#include <QObject>

#include "messages/basicmessage.h"


class MessageHandler : public QObject
{
    Q_OBJECT
public:
    explicit MessageHandler(QObject *parent = 0);

private:
    BasicMessage *m_currentMessage = nullptr;
    quint64 m_currentDeliveryTag;

signals:
    void workerFinished();
    void results(QByteArray results);
    void error(QByteArray results);
    void finishedMessage(qint64 delivery_tag);
public slots:
    void on_newMessage(QJsonDocument msg, qint64 delivery_tag);
private slots:
    void on_finished(QJsonDocument result);
    void on_error(QJsonDocument errorMsg);
};

#endif // MESSAGEHANDLER_H
