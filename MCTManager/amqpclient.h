#ifndef AMQPCLIENT_H
#define AMQPCLIENT_H

#include <QObject>
#include <QJsonDocument>
#include "qamqpclient.h"
#include "qamqpmessage.h"
#include "qamqpqueue.h"

class AmqpClient : public QObject
{
    Q_OBJECT
public:
    explicit AmqpClient(QString queueName, bool durable, int qos, bool consumer, QObject *parent = 0);
    ~AmqpClient();

protected:
    QAmqpClient m_client;
    QString m_queueName;
    QAmqpQueue *m_queue = 0;
    bool m_durable;
    bool m_consumer;
    int m_qos;

private:
    void start();


signals:
    void newMessage(QJsonDocument result, qint64 delivery_tag);
    void queueReady();

private slots:
    void on_clientConnected();
    void on_queueDeclared();
    void on_messageReceived();
    void ackMessage(qint64 delivery_tag);


public slots:
};

#endif // AMQPCLIENT_H
