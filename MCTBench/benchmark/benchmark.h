#ifndef BENCHMARK_H
#define BENCHMARK_H

#include <QObject>
#include <QVariantMap>

class Benchmark : public QObject
{
    Q_OBJECT
public:
    explicit Benchmark(QObject *parent = 0);
    ~Benchmark();
    virtual void startBenchmark();

    QList<QVariantMap> results();
protected:
    QList<QVariantMap> m_results;
private:

signals:
    void finished();

public slots:
};

#endif // BENCHMARK_H
