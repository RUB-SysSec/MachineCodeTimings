QT += core sql
QT -= gui

CONFIG += link_pkgconfig
PKGCONFIG += libmongocxx

include(qamqp/qamqp.pri)
INCLUDEPATH += -L qamqp/src
LIBS += -lqamqp

CONFIG += c++11

TARGET = Manager
CONFIG += console
CONFIG -= app_bundle

TEMPLATE = app

SOURCES += main.cpp \
    manager.cpp \
    amqpclient.cpp \
    resulthandler.cpp \
    sigwatch.cpp \
    jobmanager.cpp \
    job.cpp \
    amqpexchange.cpp \
    intrinsic.cpp

HEADERS += \
    manager.h \
    amqpclient.h \
    resulthandler.h \
    sigwatch.h \
    jobmanager.h \
    job.h \
    amqpexchange.h \
    intrinsic.h
