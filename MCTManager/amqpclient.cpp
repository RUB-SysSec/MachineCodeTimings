#include "amqpclient.h"

#include <QTimer>
#include <QJsonObject>
#include <QHostAddress>
#include <QSettings>

AmqpClient::AmqpClient(QString queueName, bool durable, int qos, bool consumer,  QObject *parent) : QObject(parent),
    m_queueName(queueName),
    m_durable(durable),
    m_qos(qos),
    m_consumer(consumer)
{
    qDebug() << "created";
    setObjectName(m_queueName);
    start();
}

AmqpClient::~AmqpClient()
{
    qDebug() << "exiting";
    m_client.disconnectFromHost();
    delete m_queue;
}

void AmqpClient::start()
{
    connect(&m_client, SIGNAL(connected()), this, SLOT(on_clientConnected()));
    //m_client.setAutoReconnect(true);

    QSettings s;
    m_client.setUsername(s.value("mq_user").toString());
    m_client.setPassword(s.value("mq_pw").toString());
    m_client.connectToHost(QHostAddress(s.value("mq_host").toString()));
}

void AmqpClient::on_clientConnected()
{
    // Result Queue
    qDebug() << "Creating queue" << m_queueName;
    
    m_queue = m_client.createQueue(m_queueName);

    m_queue->qos(m_qos);
    connect(m_queue, SIGNAL(declared()), this, SLOT(on_queueDeclared()));
    if(m_durable) m_queue->declare(QAmqpQueue::Durable);
    else m_queue->declare();

}

void AmqpClient::on_queueDeclared()
{
    if(m_consumer){
        qDebug() << "Consuming";
        connect(m_queue, SIGNAL(messageReceived()), this, SLOT(on_messageReceived()));
        m_queue->consume();
    }
    emit queueReady();
}

void AmqpClient::on_messageReceived()
{
    qDebug() << this << "Received Message";
    QAmqpMessage message = m_queue->dequeue();

    emit newMessage(QJsonDocument::fromJson(message.payload()), message.deliveryTag());
}

void AmqpClient::ackMessage(qint64 delivery_tag)
{
    qDebug() << "delivery_tag" << delivery_tag;
    m_queue->ack(delivery_tag, false);
}
