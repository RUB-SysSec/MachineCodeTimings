#include <QCoreApplication>
#include "manager.h"
#include <signal.h>
#include <unistd.h>
#include "sigwatch.h"

int main(int argc, char *argv[])
{
    QCoreApplication a(argc, argv);
    qSetMessagePattern("[%{time hh:mm:ss}] %{function}: %{message}");

    UnixSignalWatcher sigwatch;

    sigwatch.watchForSignal(SIGINT);
    sigwatch.watchForSignal(SIGTERM);

    QCoreApplication::setOrganizationName("syssec.rub.de");

    QCoreApplication::setApplicationName("MCTManager");


    Manager *m = new Manager;
    QObject::connect(&sigwatch, SIGNAL(unixSignal(int)), m, SLOT(on_unixSignal(int)));

    return a.exec();
}
