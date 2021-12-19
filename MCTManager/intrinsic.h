#ifndef INTRINSIC_H
#define INTRINSIC_H

#include <QObject>
#include <QVariantMap>


class Job;

class Intrinsic : public QObject
{
    Q_OBJECT
public:
    explicit Intrinsic(int id, int force_parameterInitCategory, Job *parent = 0);
    void buildMeasurementPrograms();
    bool valid() const;

    int id() const;

    QString compilerCommand() const;

    void build();

private:
    void loadMemberValuesFromDatabase();
    int m_id;
    Job *m_job = 0;
    // SQL values

    QString m_intrinsic;
    QString m_rettype;
    QString m_rettypeName;
    QString m_asmCommand;
    QString m_desc;
    QString m_cpuid_flags;
    QString m_include;
    QString m_compilerCommand;
    QString m_operation;
    int m_template;
    int m_category;
    bool m_saved;
    bool m_valid  = false;

    int m_force_parameterInitCategory = -1;

    bool generateParameterInitList();
    QList<QVariantMap> m_parameterList;
    QList<QList<QVariantMap> > m_parametersInitList;

    void generateOperatorsRecursive(int depth = 0);
    QList<QVariantMap> m_tmpOperator;
    QList<QList<QVariantMap> > m_allOperatorList;
    QString buildIntrinsicCommand();
    QString parameterInit(int combination, QVariantMap *usedParameters);



signals:
    void codeGenFinished();
    void templateFinished(QString code, QVariantMap usedParameters);

public slots:
};

#endif // INTRINSIC_H
