#include "mctjson.h"

MCTJson::MCTJson()
{
    m_isNull = true; // Debug
    m_tempDir.setAutoRemove(true);
}

/*MCTJson::MCTJson(QJsonDocument jsonFile):
    m_json(jsonFile)
{
    m_isNull = false; // Debug
    //m_tempDir.setAutoRemove(false);
}*/

/*MCTJson MCTJson::fromJson(QJsonDocument json)
{
    return MCTJson(json);
}
*/

QString MCTJson::filename()
{
    return QString("test.c");
}

QString MCTJson::fileContentAsBase64()
{
    return QString("#include <stdio.h>\nint main(){return 0;}");
}

QString MCTJson::compilerCommand()
{
    //return QString("gcc");
    return QString("make");
}

QStringList MCTJson::compilerCommandOptions()
{
    return QStringList();// << "-o" << "test" << "test.c";
}

bool MCTJson::isNull()
{
    return m_isNull;
}

bool MCTJson::isTar()
{
    return true;
}

QByteArray MCTJson::tarContentAsBase64()
{
    /*return  QByteArray(
            "H4sICD/71FgAA3Rlc3QudGFyAO3TvQ7CIBiFYVZ7FYy6GOjv4NUQQ1IMYkLbODTeu9TG0bGoyfsw"
            "kO+D4SynN97f9PEsNqSStq6XW3eNes16nVeVFrpSbZNeq2Wvy3SEVFuGepuG0UQpxd1Zby8mfP5n"
            "45AjUF4ujPJqXNgf5mIX7TjFINWpeBTfDoYs+qX/5Q/1v+nW/rf0Pwf6DwAAAAAAAAAAAAAA8N+e"
            "AyDs6AAoAAA="
            );*/

    return QByteArray(
                "H4sIABhE1VgAA+3TsU7DMBAGYK/1U5zK0g5E58RJJYp4A96AxXJdYnAd5DhiQLw7acrAghhQgpD+"
                "b7B1liXfSf5bE0JXWDEnHjVan3e1q3mq1aWeKK6EqripS612uhGsStYsiGft6tPQZ5OIxKt3wT2Z"
                "+P09l/olGlrWlY82DAdHt30++K5o76SPmU7Gx832TdLoJY0nx826PX+Vh7je7uUquTykSLyX7/Kv"
                "R4BfuDfP7uiDm/ONn/LP1Zf811P+S+R/GSebb+Tq0Vq67mhK+GUtLHINAAAAAAAAAAAAAAAAAAAA"
                "8B98ABWahIgAKAAA"
                );
}

void MCTJson::setJson(const QJsonDocument &json)
{
    m_json = json;
    m_isNull = false; // Debug
}

QString MCTJson::tempDirPath() const
{
    return m_tempDir.path();
}
