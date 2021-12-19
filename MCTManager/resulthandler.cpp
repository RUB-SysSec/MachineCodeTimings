#include "resulthandler.h"
#include <QDebug>
#include <QSettings>

#include <bsoncxx/builder/stream/document.hpp>
#include <bsoncxx/types.hpp>
#include <bsoncxx/json.hpp>
#include <bsoncxx/string/view_or_value.hpp>
using bsoncxx::builder::stream::document;
using bsoncxx::builder::stream::open_document;
using bsoncxx::builder::stream::close_document;
using bsoncxx::builder::stream::open_array;
using bsoncxx::builder::stream::close_array;
using bsoncxx::builder::stream::finalize;

ResultHandler::ResultHandler(QObject *parent) : QObject(parent)
{
    qDebug();
    QSettings s;

    bsoncxx::string::view_or_value conn_uri(s.value("mongodb_uri").toString().toStdString());
    m_conn = new mongocxx::client{mongocxx::uri{conn_uri}};
    if(m_conn) m_db = m_conn->database("mct_prod");
}

void ResultHandler::on_newResult(QJsonDocument result, qint64 delivery_tag)
{
    //qDebug() << result;

    bsoncxx::document::value result_bson = bsoncxx::from_json(result.toJson().toStdString());
    auto res = m_db["results"].insert_one(result_bson.view());

    emit finishedResult(delivery_tag);
}

void ResultHandler::on_newError(QJsonDocument error, qint64 delivery_tag)
{
    //qDebug() << error;
    bsoncxx::document::value error_bson = bsoncxx::from_json(error.toJson().toStdString());
    auto res = m_db["errors"].insert_one(error_bson.view());

    emit finishedError(delivery_tag);
}
