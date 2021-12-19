#include "benchmark.h"

#include <QDebug>

Benchmark::Benchmark(QObject *parent) : QObject(parent)
{
    qDebug();
}

Benchmark::~Benchmark()
{
    qDebug();
}

void Benchmark::startBenchmark()
{
    qDebug() << "not implemented";
    emit finished();
}

QList<QVariantMap> Benchmark::results()
{
    qDebug();
    return m_results;
}


