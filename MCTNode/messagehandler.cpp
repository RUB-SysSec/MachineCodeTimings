#include "messagehandler.h"
#include <QDebug>
#include <QTimer>
#include <QJsonDocument>

MessageHandler::MessageHandler(QObject *parent) : QObject(parent)
{

}

void MessageHandler::on_newMessage(QJsonDocument doc, qint64 delivery_tag)
{
    qDebug() << this << "new Message";
    m_currentDeliveryTag = delivery_tag;
    if(!doc.isNull()){
        m_currentMessage = new BasicMessage;

        connect(m_currentMessage, SIGNAL(finished(QJsonDocument)),
                this, SLOT(on_finished(QJsonDocument)));
        connect(m_currentMessage, SIGNAL(error(QJsonDocument)),
                this, SLOT(on_error(QJsonDocument)));

        m_currentMessage->on_newMessage(doc);
    }
    else{
        qDebug() << "Not a valid json message. Skipping...";
        emit finishedMessage(delivery_tag);
    }


}

void MessageHandler::on_finished(QJsonDocument result)
{
    emit finishedMessage(m_currentDeliveryTag);

    QObject::sender()->deleteLater();
    m_currentMessage = nullptr;
    emit results(result.toJson());

}

void MessageHandler::on_error(QJsonDocument errorMsg)
{
    emit finishedMessage(m_currentDeliveryTag);
    emit error(errorMsg.toJson());
}
