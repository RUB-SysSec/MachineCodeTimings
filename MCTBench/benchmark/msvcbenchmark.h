#if defined(Q_OS_WIN)
#ifndef MSVCBENCHMARK_H
#define MSVCBENCHMARK_H

#include "benchmark.h"

class MSVCBenchmark : public Benchmark
{
public:
    MSVCBenchmark(QObject *parent = 0);
    void startBenchmark();
};

#endif // MSVCBENCHMARK_H
#endif
