#ifndef MCTJSON_H
#define MCTJSON_H

#include <QJsonDocument>
#include <QStringList>
#include <QTemporaryDir>

class MCTJson
{

public:
    MCTJson();
    //MCTJson(QJsonDocument json);

    QString filename();
    QString fileContentAsBase64();
    QString compilerCommand();
    QStringList compilerCommandOptions();
    bool isNull();
    bool isTar();
    QByteArray tarContentAsBase64();

    //static MCTJson fromJson(QJsonDocument json);

    void setJson(const QJsonDocument &json);

    QString tempDirPath() const;

private:
    QJsonDocument m_json;
    bool m_isNull = false;
    QTemporaryDir m_tempDir;

};

#endif // MCTJSON_H
