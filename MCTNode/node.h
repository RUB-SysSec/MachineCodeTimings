#ifndef NODE_H
#define NODE_H

#include <QObject>
#include <QTcpServer>
#include <QList>
#include "amqpclient.h"
#include "amqpexchange.h"

class Node : public QObject
{
    Q_OBJECT
public:
    explicit Node(QObject *parent = 0);
private:
    QString m_nodeId;
    void setDefaultSettings();
signals:

public slots:
private slots:
    void on_aboutToQuit();
};

#endif // NODE_H
