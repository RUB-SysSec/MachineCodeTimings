#include "node.h"

#include <QDebug>
#include <QTcpSocket>
#include <QSettings>
#include <QUuid>
#include <QDir>
#include <QCoreApplication>

#include "messagehandler.h"

Node::Node(QObject *parent) : QObject(parent)
{
    qDebug() << this << "Starting";
    QCoreApplication::instance()->setProperty("configFilePath", QDir::currentPath()+"/node.conf");

    setDefaultSettings();

    QSettings settings(QCoreApplication::instance()->property("configFilePath").toString(),
                           QSettings::IniFormat);
    m_nodeId = settings.value("node_id").toString();

    qDebug() << "Node id" << m_nodeId;
    QCoreApplication::instance()->setProperty("nodeId", m_nodeId);

    connect(QCoreApplication::instance(), SIGNAL(aboutToQuit()), this, SLOT(on_aboutToQuit()));

    MessageHandler *msgHandler = new MessageHandler(this);

    AmqpExchange *resultsExchange = new AmqpExchange("results", true, 1, this);
    connect(msgHandler, SIGNAL(results(QByteArray)), resultsExchange, SLOT(writeMessage(QByteArray)));

    AmqpExchange *errorsExchange = new AmqpExchange("errors", true, 1, this);
    connect(msgHandler, SIGNAL(error(QByteArray)), errorsExchange, SLOT(writeMessage(QByteArray)));

    AmqpClient* nodeAmqpClient = new AmqpClient("node_"+m_nodeId, true, 1, true, this);
    connect(nodeAmqpClient, SIGNAL(newMessage(QJsonDocument, qint64)), msgHandler, SLOT(on_newMessage(QJsonDocument, qint64)));
    connect(msgHandler, SIGNAL(finishedMessage(qint64)), nodeAmqpClient, SLOT(ackMessage(qint64)));

}

void Node::setDefaultSettings()
{
    QSettings s(QCoreApplication::instance()->property("configFilePath").toString(),
                           QSettings::IniFormat);

    if(!s.value("default_settings_set").toBool()){

        s.setValue("node_id", QUuid::createUuid().toString().right(13).left(12));

        s.setValue("mq_host", "127.0.0.1");
        s.setValue("mq_user", "node");
        s.setValue("mq_pw", "insert_mq-pw_here");

        s.setValue("default_settings_set", true);
    }
}

void Node::on_aboutToQuit()
{
    qDebug() << "on_aboutToQuit";
}



