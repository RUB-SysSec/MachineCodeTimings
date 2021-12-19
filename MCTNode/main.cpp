#include <QCoreApplication>
#include "node.h"
int main(int argc, char *argv[])
{
    QCoreApplication a(argc, argv);
    qSetMessagePattern("[%{time hh:mm:ss}] %{function}: %{message}");
    qDebug() << "Node" << GIT_VERSION;
    QCoreApplication::setOrganizationName("syssec.rub");
    QCoreApplication::setApplicationName("MCTNode");

    new Node();


    return a.exec();
}
