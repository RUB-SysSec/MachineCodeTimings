#include "cpu.h"
#include <QDebug>
#include <QMetaEnum>

CPU::CPU(QObject *parent) : QObject(parent)
{
    detectFeatures();
    processorName();
    vendor();
}

CPU::~CPU()
{
    qDebug();
}

bool CPU::hasFeature(CPU::CPU_HW_FEATURE feature)
{
    /*
     * For primitive types like int and double,
     * as well as for pointer types, the C++
     * language doesn't specify any initialization;
     * in those cases, Qt's containers automatically
     * initialize the value to 0.
     * http://doc.qt.io/qt-5/containers.html#default-constructed-value
     */

    return m_cpuFeatures[feature];
}

QString CPU::vendor()
{
    if(m_vendor.isEmpty()){
        // https://www.lowlevel.eu/wiki/CPUID
        QByteArray vendorIdString;
        int info[4];
        cpuid(info, 0x00000000);
        vendorIdString.append((char*)info+4, 4);
        vendorIdString.append((char*)info+12, 4);
        vendorIdString.append((char*)info+8, 4);

        m_vendor = QString(vendorIdString);
        qDebug() << "Vendor:" << m_vendor;
    }

    return m_vendor;
}

QString CPU::processorName()
{
    // https://www.lowlevel.eu/wiki/CPUID
    if(m_processorName.isEmpty()){
        QByteArray processorName;
        int info[4];
        cpuid(info, 0x80000002);
        processorName.append((char*)info, 16);
        cpuid(info, 0x80000003);
        processorName.append((char*)info, 16);
        cpuid(info, 0x80000004);
        processorName.append((char*)info, 16);

        m_processorName = QString(processorName);
        qDebug() << "CPU-Model:" << m_processorName;
    }

    return m_processorName;
}


void CPU::detectFeatures()
{
    // http://stackoverflow.com/questions/6121792/how-to-check-if-a-cpu-supports-the-sse3-instruction-set
    qDebug() << "Detecting CPU Features";
    int info[4];
    cpuid(info, 0);

    int nIds = info[0];

    cpuid(info, 0x80000000);
    unsigned nExIds = info[0];

    if (nIds >= 0x00000001){
        cpuid(info, 0x00000001);

        m_cpuFeatures[CPU_HW_FEATURE::MMX]    = (info[3] & ((int)1 << 23)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::SSE]    = (info[3] & ((int)1 << 25)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::SSE2]   = (info[3] & ((int)1 << 26)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::SSE3]   = (info[2] & ((int)1 <<  0)) != 0;

        m_cpuFeatures[CPU_HW_FEATURE::SSSE3]  = (info[2] & ((int)1 <<  9)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::SSE41]  = (info[2] & ((int)1 << 19)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::SSE42]  = (info[2] & ((int)1 << 20)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::AES]    = (info[2] & ((int)1 << 25)) != 0;

        m_cpuFeatures[CPU_HW_FEATURE::AVX]    = (info[2] & ((int)1 << 28)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::FMA3]   = (info[2] & ((int)1 << 12)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::RDRAND] = (info[2] & ((int)1 << 30)) != 0;

    }

    if (nIds >= 0x00000007){
        cpuid(info, 0x00000007);
        m_cpuFeatures[CPU_HW_FEATURE::AVX2]        = (info[1] & ((int)1 <<  5)) != 0;

        m_cpuFeatures[CPU_HW_FEATURE::BMI1]        = (info[1] & ((int)1 <<  3)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::BMI2]        = (info[1] & ((int)1 <<  8)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::ADX]         = (info[1] & ((int)1 << 19)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::SHA]         = (info[1] & ((int)1 << 29)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::PREFETCHWT1] = (info[2] & ((int)1 <<  0)) != 0;

        m_cpuFeatures[CPU_HW_FEATURE::AVX512F]     = (info[1] & ((int)1 << 16)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::AVX512CD]    = (info[1] & ((int)1 << 28)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::AVX512PF]    = (info[1] & ((int)1 << 26)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::AVX512ER]    = (info[1] & ((int)1 << 27)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::AVX512VL]    = (info[1] & ((int)1 << 31)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::AVX512BW]    = (info[1] & ((int)1 << 30)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::AVX512DQ]    = (info[1] & ((int)1 << 17)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::AVX512IFMA]  = (info[1] & ((int)1 << 21)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::AVX512VBMI]  = (info[2] & ((int)1 <<  1)) != 0;
    }
    if (nExIds >= 0x80000001){
        cpuid(info, 0x80000001);
        m_cpuFeatures[CPU_HW_FEATURE::x64]   = (info[3] & ((int)1 << 29)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::ABM]   = (info[2] & ((int)1 <<  5)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::SSE4a] = (info[2] & ((int)1 <<  6)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::FMA4]  = (info[2] & ((int)1 << 16)) != 0;
        m_cpuFeatures[CPU_HW_FEATURE::XOP]   = (info[2] & ((int)1 << 11)) != 0;
    }

    QMetaEnum metaEnum_CPU_HW = QMetaEnum::fromType<CPU_HW_FEATURE>();

    QStringList supported, unsupported;
    foreach(CPU_HW_FEATURE feature, m_cpuFeatures.keys()){
        if(m_cpuFeatures[feature]) supported << metaEnum_CPU_HW.valueToKey((int)feature);
        else unsupported << metaEnum_CPU_HW.valueToKey((int)feature);
    }
    qDebug() << "Supported:" << supported;
    qDebug() << "Unsupported:" << unsupported;
}
