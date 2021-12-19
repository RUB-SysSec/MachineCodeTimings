QT += core network
QT -= gui

include(qamqp/qamqp.pri)
INCLUDEPATH += -L qamqp/src
LIBS += -lqamqp

GIT_VERSION = $$system(git --git-dir $$PWD/.git --work-tree $$PWD describe --always --tags)
DEFINES += GIT_VERSION=\\\"$$GIT_VERSION\\\"

CONFIG += c++11

TARGET = Node
CONFIG += console
CONFIG -= app_bundle

TEMPLATE = app

SOURCES += main.cpp \
    node.cpp \
    amqpclient.cpp \
    messagehandler.cpp \
    messages/basicmessage.cpp \
    amqpexchange.cpp \
    messages/mctjson.cpp

HEADERS += \
    node.h \
    amqpclient.h \
    messagehandler.h \
    messages/basicmessage.h \
    amqpexchange.h \
    messages/mctjson.h

STATECHARTS +=

RESOURCES +=
