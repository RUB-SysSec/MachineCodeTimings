#include "basicmessage.h"
#include <QDebug>

#include <QJsonObject>
#include <QJsonArray>
#include <QByteArray>
#include <QCoreApplication>
#include <QMetaEnum>

BasicMessage::BasicMessage(QObject *parent) : QObject(parent)
{
    connect(this, SIGNAL(compileFinished(QByteArray, QByteArray, QProcess::ExitStatus)), this, SLOT(disasmCommand()));
    connect(this, SIGNAL(disasmFinished(QByteArray,QByteArray,QProcess::ExitStatus)), this, SLOT(runCommand()));
    connect(this, SIGNAL(runFinished(QByteArray,QByteArray,QProcess::ExitStatus)),
            this, SLOT(on_runFinished(QByteArray,QByteArray,QProcess::ExitStatus)));
}

void BasicMessage::on_newMessage(QJsonDocument msg)
{
    qDebug() << "on_newMessage";
    m_json = msg;
    QByteArray command = QByteArray::fromBase64(m_json.object()["command"].toString().toLatin1());

    m_sourceFilePath = "/tmp/test.c";
    QFile sourceFile(m_sourceFilePath);
    sourceFile.remove();

    m_executeableFilePath = "/tmp/test";
    QFile exeFile(m_executeableFilePath);
    exeFile.remove();

    m_compiler = m_json.object()["compiler"].toString();
    m_compilerOptions = m_json.object()["compiler_options"].toString();

    m_repetitions = m_json.object()["repetitions"].toInt();
    m_jobId = m_json.object()["job_id"].toString();
    m_instructionId = m_json.object()["intrinsic_id"].toInt();

    if(writeCommandToFile(command)){
        // Compile etc;
        compileCommand();
    }

}

bool BasicMessage::writeCommandToFile(QByteArray command)
{
    qDebug() << "writeCommandToFile";
    m_commandFile.setFileName(m_sourceFilePath);
    if(m_commandFile.open(QIODevice::WriteOnly)){
        m_commandFile.write(command);
        m_commandFile.close();
        return true;
    }
    else{
        qWarning() << "Could not open" << m_commandFile.fileName();
        return false;
    }
    return false;
}

void BasicMessage::compileCommand()
{
    qDebug() << "compileCommand";
    QProcess* process = new QProcess(this);
    process->setProperty("type", "compile");
    process->setWorkingDirectory("/tmp/");

    connect(process, SIGNAL(finished(int, QProcess::ExitStatus)),
            this, SLOT(on_processFinished(int, QProcess::ExitStatus)));

    QStringList arguments;
    arguments.append(m_compilerOptions.split(" "));
    arguments << m_commandFile.fileName() << "-o" <<  m_executeableFilePath;
    process->start(m_compiler, arguments);
}

void BasicMessage::disasmCommand()
{
    qDebug() << "disasmCommand";
    QProcess* process = new QProcess(this);
    process->setProperty("type", "disasm");

    connect(process, SIGNAL(finished(int, QProcess::ExitStatus)),
            this, SLOT(on_processFinished(int, QProcess::ExitStatus)));

    QStringList arguments;
    arguments << "-d" << "-M" << "intel" << "-S" << m_executeableFilePath;
    process->start("objdump", arguments);
}

void BasicMessage::runCommand()
{
    qDebug() << "runCommand";
    QProcess* process = new QProcess(this);
    process->setProperty("type", "run");
    process->setWorkingDirectory("/tmp/");

    connect(process, SIGNAL(finished(int, QProcess::ExitStatus)),
            this, SLOT(on_processFinished(int, QProcess::ExitStatus)));

    QStringList arguments;
    process->start(m_executeableFilePath);
}

void BasicMessage::on_processFinished(int exitCode, QProcess::ExitStatus exitStatus)
{
    QProcess* process = qobject_cast<QProcess*>(QObject::sender());
    qDebug() << "Process (" << process->property("type").toString() << ") finished with" << exitStatus << "and exitCode" << exitCode;

    if(exitCode == 0){

        if(process->property("type").toString() == "run")
            emit runFinished(process->readAllStandardOutput(),
                             process->readAllStandardError(),
                             exitStatus);

        if(process->property("type").toString() == "compile")
            emit compileFinished(process->readAllStandardOutput(),
                                 process->readAllStandardError(),
                                 exitStatus);

        if(process->property("type").toString() == "disasm"){
            m_asm = process->readAllStandardOutput();
            emit disasmFinished(m_asm,
                                process->readAllStandardError(),
                                exitStatus);
        }
    }
    else{
        on_error(process);
    }

    process->deleteLater();
}

void BasicMessage::on_error(QProcess *process)
{
    qDebug() << "on_error";

    QJsonDocument jsonDoc;
    QJsonObject errorMsg;

    errorMsg["job_id"] = m_jobId;
    errorMsg["node_id"] = QCoreApplication::instance()->property("nodeId").toString();
    errorMsg["instruction_id"] = m_instructionId;
    errorMsg["parameters"] = m_json.object()["parameters"];
    errorMsg["stdout"] = QString(process->readAllStandardOutput().toBase64());
    errorMsg["stderr"] = QString(process->readAllStandardError().toBase64());
    errorMsg["exit_code"] = process->exitCode();
    errorMsg["state"] = process->property("type").toString();

    jsonDoc.setObject(errorMsg);
    emit error(jsonDoc);
}

void BasicMessage::on_runFinished(QByteArray stdout, QByteArray stderr, QProcess::ExitStatus exitStatus)
{
    qDebug() << "on_runFinished";
    //qDebug() << stdout << stderr << exitStatus;
    m_resultList.append(QJsonDocument::fromJson(stdout));
    if(m_resultList.size() < m_repetitions + 1){
        qDebug() << "Repetitions left: " + QString::number(m_repetitions - m_resultList.size());
        runCommand();
    }
    else{
        QJsonDocument result = m_resultList.takeFirst();

        QJsonObject root;
        root["job_id"] = m_jobId;
        root["node_id"] = QCoreApplication::instance()->property("nodeId").toString();
        root["results"] = result.object();
        root["instruction_id"] = m_instructionId;

        //Only save main function
//        QStringList asmLines = QString(m_asm).split("\n");
//        QList<QString>::const_iterator i;
//        QStringList final_asm;
//        bool inMain = false;
//        for (i = asmLines.cbegin(); i != asmLines.cend(); ++i){

//            if((*i).contains("<benchmark>:") || inMain){
//                 inMain = true;
//                 final_asm.append((*i));
//                 if((*i).isEmpty()) inMain = false;
//            }
//        }
//        root["asm"] = final_asm.join("\n");

        int start = m_asm.lastIndexOf('\n', m_asm.indexOf("<benchmark_loop_start>:"));
        start++;
        int end = m_asm.indexOf('\n', m_asm.indexOf("<benchmark_loop_end>:"));
        end--;
        int length = end - start;
        QString cut_asm(m_asm.mid(start, length));

        root["asm"] = cut_asm;

        root["search_for_string_success"] = cut_asm.contains(m_json.object()["search_for_string"].toString());

        QJsonObject avgResults;
        root["parameters"] = m_json.object()["parameters"];

        qDebug() << result;
        emit finished(QJsonDocument(root));
    }
}

