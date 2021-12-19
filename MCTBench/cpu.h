#ifndef CPU_H
#define CPU_H

#include <QMap>
#include <QObject>

class CPU: public QObject
{
    Q_OBJECT

public:
    CPU(QObject *parent = 0);
    ~CPU();

    enum class CPU_HW_FEATURE{
        MMX,
        SSE, SSE2, SSE3, SSSE3, SSE4a, SSE41, SSE42,
        AES,
        AVX, AVX2,
        FMA3, FMA4,
        RDRAND,
        SHA,
        x64,
        BMI1, BMI2,
        ADX,
        PREFETCHWT1,
        AVX512F, AVX512CD, AVX512PF, AVX512ER, AVX512VL, AVX512BW, AVX512DQ, AVX512IFMA, AVX512VBMI,
        ABM,
        XOP

    };
    Q_ENUM(CPU_HW_FEATURE)

    bool hasFeature(CPU_HW_FEATURE feature);
    QString vendor();
    QString processorName();

private:

#if defined(Q_OS_WIN)

//  Windows
    #define cpuid(info, x)    __cpuidex(info, x, 0)

#elif defined(Q_OS_LINUX)

    #include <cpuid.h>
    void cpuid(int *info, int infoType){
        __cpuid_count(infoType, 0, info[0], info[1], info[2], info[3]);
    }

#else
    #error Platform not supported
#endif

    void detectFeatures();

    QMap<CPU_HW_FEATURE, bool> m_cpuFeatures;
    QString m_vendor;
    QString m_processorName;


};

#endif // CPU_H
