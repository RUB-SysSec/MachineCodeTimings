#include <QtGlobal>
#if defined(Q_OS_WIN)
#include "msvcbenchmark.h"

#include <QDebug>
#include <intrin.h>
MSVCBenchmark::MSVCBenchmark(QObject *parent) : Benchmark(parent)
{

}

void MSVCBenchmark::startBenchmark()
{
    qDebug();
    emit finished();
}
#endif
