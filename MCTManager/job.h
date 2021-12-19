#ifndef JOB_H
#define JOB_H

#include <QObject>
#include <QJsonDocument>
#include <QMap>
#include <QSqlDatabase>
#include "amqpexchange.h"
#include "intrinsic.h"

class Job : public QObject
{
    Q_OBJECT
public:
    explicit Job(QJsonDocument json, qint64 delivery_tag, QObject *parent = 0);
    ~Job();


    QString intrinsicTemplate() const;


    int loopSize() const;

    QMap<int, QString> templateMap() const;

private:
    QJsonDocument m_json;
    qint64 m_delivery_tag;
    QMap<QString, AmqpExchange*> m_exchangeMap;
    QMap<AmqpExchange*, bool> m_exchangeRdyMap;
    QSqlDatabase m_db;
    QString m_intrinsicTemplate;
    int m_loopSize;
    QList<Intrinsic*> m_intrinsicList;
    QMap<int, QString> m_templateMap;

    // Remove later
    int m_i = 0;

signals:
    void finished(qint64 delivery_tag);
    void finished(QJsonDocument job);
    void finishedThread();
    void allExchangesRdy();
    void connectedToSqlDatabase();

private slots:
    void on_exchangeReady();
    void on_allExchangesRdy();
    void connectToSqlDatabase();
    void on_connectedToSqlDatabase();

    void on_codeGenFinished();
    void on_templateFinished(QString filledTemplate, QVariantMap usedParameters);

    void nextIntrinsic();

public slots:
    void process();
    void done();
};

#endif // JOB_H
