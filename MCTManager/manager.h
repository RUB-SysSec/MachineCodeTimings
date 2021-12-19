#ifndef MANAGER_H
#define MANAGER_H

#include <QObject>
#include "amqpclient.h"
#include "resulthandler.h"
#include "jobmanager.h"
#include <mongocxx/instance.hpp>
class Manager : public QObject
{
    Q_OBJECT
public:
    explicit Manager(QObject *parent = 0);
    ~Manager();
private:
    mongocxx::instance m_inst{};
    ResultHandler *m_resultHandler;
    JobManager *m_jobManager;

    void setDefaultSettings();
signals:

public slots:
private slots:
    void on_aboutToQuit();
    void on_unixSignal(int signal);
};

#endif // MANAGER_H
