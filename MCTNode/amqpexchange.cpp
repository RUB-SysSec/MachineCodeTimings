#include "amqpexchange.h"
#include "qamqpexchange.h"
AmqpExchange::AmqpExchange(QString queueName, bool durable, int qos, QObject *parent) : AmqpClient(queueName, durable, qos, false, parent)
{
    connect(this, SIGNAL(queueReady()), this, SLOT(on_queueReady()));
}

AmqpExchange::~AmqpExchange()
{
    qDebug();
}

void AmqpExchange::on_queueReady()
{
    m_resultExchange = m_client.createExchange();
    m_resultExchange->enableConfirms();
    emit exchangeReady();
}

void AmqpExchange::writeMessage(QByteArray msg)
{
    QAmqpMessage::PropertyHash properties;
    properties[QAmqpMessage::DeliveryMode] = "2";
    properties[QAmqpMessage::ContentType] = "application/json";

    m_resultExchange->publish(msg, m_queueName, properties);
    m_resultExchange->waitForConfirms();
}
