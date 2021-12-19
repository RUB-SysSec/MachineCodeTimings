#include <QtGlobal>
#if defined(Q_OS_LINUX)
#ifndef GCCBENCHMARK_H
#define GCCBENCHMARK_H

#include "benchmark.h"

class GCCBenchmark : public Benchmark
{
public:
    GCCBenchmark(QObject *parent = 0);
    ~GCCBenchmark();
    void startBenchmark();
private:
    inline void rdtsc_warmup(unsigned* cycles_low_start, unsigned* cycles_high_start, unsigned* cycles_low_end, unsigned* cycles_high_end) __attribute__((always_inline));
    inline void rdtsc_start(unsigned* cycles_low_start, unsigned* cycles_high_start) __attribute__((always_inline));
    inline void rdtsc_end(unsigned* cycles_low_end, unsigned* cycles_high_end) __attribute__((always_inline));
};

#endif // GCCBENCHMARK_H

#endif
