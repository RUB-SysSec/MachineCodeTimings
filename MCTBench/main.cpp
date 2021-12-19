#include <QCoreApplication>

#include "mctbench.h"

int main(int argc, char *argv[])
{
    QCoreApplication a(argc, argv);
    QCoreApplication::setOrganizationName("syssec.rub");
    QCoreApplication::setApplicationName("MCTBench");
    qSetMessagePattern("[DEBUG] [%{time hh:mm:ss}] %{function}: %{message}");

    MCTBench bench;
    QObject::connect(&a, SIGNAL(aboutToQuit()), &bench, SLOT(on_aboutToQuit()));

    return a.exec();
}
