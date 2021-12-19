#ifndef AMQPEXCHANGE_H
#define AMQPEXCHANGE_H

#include "qamqpclient.h"
#include "qamqpmessage.h"
#include "qamqpqueue.h"

#include "amqpclient.h"

class AmqpExchange : public AmqpClient
{
    Q_OBJECT
public:
    AmqpExchange(QString queueName, bool durable, int qos, QObject* parent = 0);
    ~AmqpExchange();

private:
    QAmqpExchange* m_resultExchange = 0;

private slots:
    void on_queueReady();

public slots:
    void writeMessage(QByteArray msg);

signals:
    void exchangeReady();
};

#endif // AMQPEXCHANGE_H

