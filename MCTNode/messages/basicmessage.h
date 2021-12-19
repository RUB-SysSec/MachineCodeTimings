#ifndef BASICMESSAGE_H
#define BASICMESSAGE_H

#include <QObject>
#include <QJsonDocument>
#include <QProcess>
#include <QFile>

class BasicMessage : public QObject
{
    Q_OBJECT
public:
    explicit BasicMessage(QObject *parent = 0);
private:
    QJsonDocument m_json;

    bool writeCommandToFile(QByteArray command);
    QFile m_commandFile;
    QString m_executeableFilePath;
    QString m_sourceFilePath;

    QString m_compiler;
    QString m_compilerOptions;

    QByteArray m_asm;

    int m_repetitions = 0;
    QString m_jobId;
    int m_instructionId;


    QList<QJsonDocument> m_resultList;

signals:
    void finished(QJsonDocument result);
    void error(QJsonDocument error);

    void compileFinished(QByteArray stdout, QByteArray stderr, QProcess::ExitStatus exitStatus);
    void runFinished(QByteArray stdout, QByteArray stderr, QProcess::ExitStatus exitStatus);
    void disasmFinished(QByteArray stdout, QByteArray stderr, QProcess::ExitStatus exitStatus);


public slots:
    void on_newMessage(QJsonDocument msg);

private slots:
    void on_processFinished(int exitCode, QProcess::ExitStatus exitStatus);
    void on_error(QProcess *process);

    void on_runFinished(QByteArray stdout, QByteArray stderr, QProcess::ExitStatus exitStatus);
    void runCommand();
    void compileCommand();
    void disasmCommand();
};

#endif // BASICMESSAGE_H
