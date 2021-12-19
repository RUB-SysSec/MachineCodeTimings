#include <QtGlobal>
#if defined(Q_OS_LINUX)
#include "gccbenchmark.h"
#include "emmintrin.h"
#include <QDebug>
#include <QList>
#include <QVariant>
#include <float.h>

GCCBenchmark::GCCBenchmark(QObject *parent): Benchmark(parent)
{
    qDebug();
}

GCCBenchmark::~GCCBenchmark()
{
    qDebug();
}

void GCCBenchmark::startBenchmark()
{
    qDebug();

    // Init
    __m128 p1_128, p2_128;
    uint64_t start, end;
    unsigned cycles_low_start = 0, cycles_high_start = 0,
             cycles_low_end = 0 , cycles_high_end = 0;
    int i = 0;
    QVector<quint64> results(10);
    quint64 *pResults = results.data();
    //

    // _mm_div_ps
    p1_128 = _mm_set_ps (1.0, 1.0, 1.0, 1.0); // 71
    p2_128 = _mm_set_ps (1.0, FLT_MAX, 1.0, FLT_MAX); // 72

    rdtsc_warmup(&cycles_low_start, &cycles_high_start, &cycles_low_end, &cycles_high_end);


    for(i = 0; i<10; i++){
        rdtsc_start(&cycles_low_start, &cycles_low_start);
         __m128 result = _mm_div_ps(p1_128, p2_128); // 472
        rdtsc_end(&cycles_low_end, &cycles_low_end);

        start = ( ((quint64)cycles_high_start << 32) | cycles_low_start );
        end = ( ((quint64)cycles_high_end << 32) | cycles_low_end );
        pResults[i] = end-start;
    }


    qDebug() << "Time:" << results;
    QList<QVariant> resultList;
    foreach(quint64 singleResult, results){
        resultList.append(QVariant(singleResult));
    }

    QVariantMap test;
    test["id"] = 472;
    test["parameters"] = QList<QVariant>() << 71 << 72;
    test["results"] = resultList;
    m_results.append(test);

    // More Intrinsics

    emit finished();
}



inline void GCCBenchmark::rdtsc_warmup(unsigned* cycles_low_start, unsigned* cycles_high_start,
                                       unsigned* cycles_low_end, unsigned* cycles_high_end)
{

    asm volatile ("CPUID\n\t"
    "RDTSC\n\t"
    "mov %%edx, %0\n\t"
    "mov %%eax, %1\n\t": "=r" (*cycles_high_start), "=r" (*cycles_low_start):: "%rax", "%rbx", "%rcx", "%rdx");

    asm volatile("RDTSCP\n\t"
    "mov %%edx, %0\n\t"
    "mov %%eax, %1\n\t"
    "CPUID\n\t": "=r" (*cycles_high_end), "=r" (*cycles_low_end):: "%rax", "%rbx", "%rcx", "%rdx");

    asm volatile ("CPUID\n\t"
    "RDTSC\n\t"
    "mov %%edx, %0\n\t"
    "mov %%eax, %1\n\t": "=r" (*cycles_high_start), "=r" (*cycles_low_start):: "%rax", "%rbx", "%rcx", "%rdx");

    asm volatile("RDTSCP\n\t"
    "mov %%edx, %0\n\t"
    "mov %%eax, %1\n\t"
    "CPUID\n\t": "=r" (*cycles_high_end), "=r" (*cycles_low_end):: "%rax", "%rbx", "%rcx", "%rdx");


}

void GCCBenchmark::rdtsc_end(unsigned *cycles_low_end, unsigned *cycles_high_end)
{
    asm volatile("RDTSCP\n\t"
    "mov %%edx, %0\n\t"
    "mov %%eax, %1\n\t"
    "CPUID\n\t": "=r" (*cycles_high_end), "=r" (*cycles_low_end):: "%rax", "%rbx", "%rcx", "%rdx");
}

void GCCBenchmark::rdtsc_start(unsigned* cycles_low_start, unsigned* cycles_high_start)
{
    asm volatile ("CPUID\n\t"
    "RDTSC\n\t"
    "mov %%edx, %0\n\t"
    "mov %%eax, %1\n\t": "=r" (*cycles_high_start), "=r" (*cycles_low_start):: "%rax", "%rbx", "%rcx", "%rdx");
}
#endif

