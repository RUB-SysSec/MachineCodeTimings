#ifndef MCTBENCH_H
#define MCTBENCH_H

#include <QObject>
#include <QMap>
#include <QTextStream>
#include <QNetworkAccessManager>
#include "cpu.h"
#include "benchmark/benchmark.h"

class MCTBench : public QObject
{
    Q_OBJECT
public:
    explicit MCTBench(QObject *parent = 0);
    ~MCTBench();
private:
    CPU *m_cpu;
    QNetworkAccessManager *m_nam;
    Benchmark *m_benchmark;
    void questionUser();

    inline QTextStream& qStdout()
    {
        static QTextStream r{stdout};
        return r;
    }

    void submitResults();

signals:

public slots:
private slots:
    void on_aboutToQuit();
    void on_benchmarkFinished();

};

#endif // MCTBENCH_H
