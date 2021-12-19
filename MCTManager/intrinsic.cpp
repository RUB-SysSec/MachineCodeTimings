#include "intrinsic.h"

#include <QDebug>
#include <QSqlQuery>
#include <QSqlError>

#include "job.h"

Intrinsic::Intrinsic(int id, int force_parameterInitCategory, Job *parent) : QObject(parent),
    m_id(id),
    m_force_parameterInitCategory(force_parameterInitCategory),
    m_job(parent)
{

}

void Intrinsic::build()
{
    loadMemberValuesFromDatabase();

    if(generateParameterInitList()){
        generateOperatorsRecursive();
        m_valid = true;
    }

    setObjectName(m_intrinsic);
}

void Intrinsic::loadMemberValuesFromDatabase()
{
    qDebug ();
    QSqlQuery query_intrinsics("SELECT * FROM intrinsics WHERE id='"+QString::number(m_id)+"'");
    while (query_intrinsics.next()) {
        m_intrinsic = query_intrinsics.value("intrinsic").toString();
        m_rettype = query_intrinsics.value("rettype").toString();
        m_asmCommand = query_intrinsics.value("asm").toString();
        m_desc = query_intrinsics.value("description").toString();
        m_cpuid_flags = query_intrinsics.value("cpuid_flags").toString();
        m_include = query_intrinsics.value("include").toString();
        m_operation = query_intrinsics.value("operation").toString();
        m_category = query_intrinsics.value("category").toInt();
        m_template = query_intrinsics.value("template_id").toInt();
        m_compilerCommand = query_intrinsics.value("compiler_command").toString();
        m_saved = query_intrinsics.value("saved").toBool();
    }
    // Get rettypeName
    QSqlQuery query_rettypeName("SELECT * FROM parameter_types WHERE id='"+m_rettype+"'");
    if(query_rettypeName.size() != 1) qWarning() << this << "Could not get rettypename";
    else{
        while (query_rettypeName.next()) {
            m_rettypeName = query_rettypeName.value("type").toString();
        }
    }

    // Gettings parameters of intrinsic
    QSqlQuery query_parameters("SELECT * FROM intrinsic_parameters WHERE intrinsic_id='"+QString::number(m_id)+"'");
    //qDebug() << this << "Parameter count:" << query_parameters.size();

    while (query_parameters.next()) {
        QVariantMap parameter;
        parameter["name"] = query_parameters.value("name").toString();
        parameter["parameter_id"] = query_parameters.value("id").toString();

        QString id = query_parameters.value("parameter_type_id").toString();
        QSqlQuery query_parametertype("SELECT * FROM parameter_types WHERE id='"+id+"'");

        if(query_parametertype.size() != 1) qWarning() << this << "Parameter query size != 1";
        while (query_parametertype.next()) {
            parameter["type"] = query_parametertype.value("type").toString();
            parameter["id"] = query_parametertype.value("id").toString();
        }
        m_parameterList.append(parameter);
        qDebug() << this << m_parameterList;
    }

    qDebug() << m_intrinsic << "loaded";

}

QString Intrinsic::compilerCommand() const
{
    return m_compilerCommand;
}

int Intrinsic::id() const
{
    return m_id;
}

bool Intrinsic::valid() const
{
    return m_valid;
}

bool Intrinsic::generateParameterInitList()
{
    qDebug();
    // Get Parameter Initializers for each Parameter
    for(int i = 0; i < m_parameterList.size(); i++){
        QList<QVariantMap> parameterInitList;

        if(m_parameterList[i]["type"] == "void"){
            qDebug () << this << "Parameter is void";
            QVariantMap parameterInit;
            parameterInitList.append(parameterInit);
        }
        else{
            if(m_force_parameterInitCategory != -1){
            qDebug() << "Forcing parameterInitCategory to" << m_force_parameterInitCategory;

                // Forcing a category
                QSqlQuery query_parameterInit;
                query_parameterInit.prepare("select * from `parameter_type_inits` inner join `parameter_type_init_parameter_type_init_category` on `parameter_type_inits`.`id` = `parameter_type_init_parameter_type_init_category`.`parameter_type_init_id` where `parameter_type_init_parameter_type_init_category`.`parameter_type_init_category_id` = :parameter_type_init_category and `parameter_type_id` = :parameter_type_id");
                query_parameterInit.bindValue(":parameter_type_id", m_parameterList[i]["id"].toString());
                query_parameterInit.bindValue(":parameter_type_init_category", m_force_parameterInitCategory);
                if(!query_parameterInit.exec()){
                    qWarning() << this << "Parametergens query with forced category failed";
                    qWarning() << query_parameterInit.lastError().text();
                }
                else{
                    if(query_parameterInit.size() > 0){
                        while(query_parameterInit.next()){
                            QVariantMap parameterInit;
                            parameterInit["code"] = query_parameterInit.value("code").toString();
                            parameterInit["id"] = query_parameterInit.value("id").toInt();
                            parameterInit["precode"] = query_parameterInit.value("precode").toString();
                            parameterInitList.append(parameterInit);
                        }
                    }
                    else{
                        qWarning() << "Error: Could not find initializer for parameter:" + m_parameterList[i]["name"].toString() +", pos: "+ QString::number(i);

                        //return false;
                    }
                }

            }
            else{

                QSqlQuery query_parameterInit;

                query_parameterInit.prepare("select * from `parameter_type_inits` inner join `intrinsic_parameter_parameter_type_init` on `parameter_type_inits`.`id` = `intrinsic_parameter_parameter_type_init`.`parameter_type_init_id` where `intrinsic_parameter_parameter_type_init`.`intrinsic_parameter_id` = :intrinsic_parameter_id");
                query_parameterInit.bindValue(":intrinsic_parameter_id", m_parameterList[i]["parameter_id"].toString());

                if(!query_parameterInit.exec()){
                    qWarning() << this << "Parametergens Query over pivot table failed";
                    qWarning() << query_parameterInit.lastError().text();
                }
                else{
                    if(query_parameterInit.size() > 0){
                        while(query_parameterInit.next()){
                            QVariantMap parameterInit;
                            parameterInit["code"] = query_parameterInit.value("code").toString();
                            parameterInit["id"] = query_parameterInit.value("id").toInt();
                            parameterInit["precode"] = query_parameterInit.value("precode").toString();
                            parameterInitList.append(parameterInit);
                        }
                    }
                    else{
                        // If instruction has no active parameters use all
                        //query_parameterInit.prepare("SELECT * FROM parameter_type_inits WHERE parameter_type_id=:parameter_type_id AND enabled='1' AND parameter_type_init_category_id='1'");
                        QSqlQuery default_cat_query("select * from parameter_type_init_categories where is_default='1'");
                        QString default_cat_id;
                        if(default_cat_query.size() > 0){
                            default_cat_query.first();
                            default_cat_id = default_cat_query.value("id").toString();
                            qDebug() << "Default cat" << default_cat_query.value("name").toString();
                        }
                        else qWarning() << "Could not get default category";


                        query_parameterInit.prepare("select * from `parameter_type_inits` inner join `parameter_type_init_parameter_type_init_category` on `parameter_type_inits`.`id` = `parameter_type_init_parameter_type_init_category`.`parameter_type_init_id` where `parameter_type_init_parameter_type_init_category`.`parameter_type_init_category_id` = :parameter_type_init_category and `parameter_type_id` = :parameter_type_id");
                        query_parameterInit.bindValue(":parameter_type_id", m_parameterList[i]["id"].toString());
                        query_parameterInit.bindValue(":parameter_type_init_category", default_cat_id);
                        if(!query_parameterInit.exec()){
                            qWarning() << this << "Parametergens Query failed";
                            qWarning() << query_parameterInit.lastError().text();
                        }
                        else{
                            if(query_parameterInit.size() > 0){
                                while(query_parameterInit.next()){
                                    QVariantMap parameterInit;
                                    parameterInit["code"] = query_parameterInit.value("code").toString();
                                    parameterInit["id"] = query_parameterInit.value("id").toInt();
                                    parameterInit["precode"] = query_parameterInit.value("precode").toString();
                                    parameterInitList.append(parameterInit);
                                }
                            }
                            else{
                                qWarning() << "Error: Could not find initializer for parameter:" + m_parameterList[i]["name"].toString() +", pos: "+ QString::number(i);

                                //return false;
                            }
                        }
                    }
                }
            }
        }
        m_parametersInitList.append(parameterInitList);
    }
    return true;
}

void Intrinsic::generateOperatorsRecursive(int depth)
{

    if(depth == m_parameterList.size()){
        m_allOperatorList.append(m_tmpOperator);
        return;
    }

    for(int i = 0; i < m_parametersInitList[depth].size(); i++){
        m_tmpOperator.append(m_parametersInitList[depth][i]);
        generateOperatorsRecursive(depth+1);
        m_tmpOperator.pop_back();
    }

}

QString Intrinsic::buildIntrinsicCommand()
{

    QString fullCommand;
    if(m_rettypeName == "void") fullCommand += "    "+m_intrinsic+"(";
    else fullCommand += "    "+m_rettypeName+" ret = "+m_intrinsic+"(";

    int parameterCount = m_parameterList.size();
    for(int i = 0; i < parameterCount; i++){
        QVariantMap parameter = m_parameterList.at(i);
        if(parameter["type"] != "void") fullCommand += parameter["name"].toString();
        if(i < parameterCount-1) fullCommand += ", ";
    }
    fullCommand += ");\n";

    return fullCommand;
}

QString Intrinsic::parameterInit(int combination, QVariantMap *usedParameters)
{
    QString fullCommand;

    // Parameter init for command
    int i=0;
    foreach(QVariantMap parameter, m_parameterList){
        if(parameter["type"] != "void"){
            fullCommand += "  " + m_allOperatorList[combination][i]["precode"].toString() + "\n";
            fullCommand += "  " + parameter["type"].toString() + " " + parameter["name"].toString() + " = " + m_allOperatorList[combination][i]["code"].toString() + "\n";
            usedParameters->insert(parameter["name"].toString(), m_allOperatorList[combination][i]["id"].toInt());

        }
        i++;
    }
    fullCommand += "\n";
    //
    return fullCommand;
}

void Intrinsic::buildMeasurementPrograms()
{
    qDebug() << this;
    qDebug() << "Building" << m_allOperatorList.size() << "Templates";
    for(int i = 0; i < m_allOperatorList.size();  i++){
        QVariantMap usedParameters;
        QString intrinsicTemplate =  m_job->templateMap()[m_template];

        if(m_include.isEmpty()) intrinsicTemplate.replace("%%INSTRUCTION_INCLUDE%%", "");
        else{
            QStringList includes = m_include.split(",");
            QString final_include;
            foreach(QString include, includes){
                if(include.trimmed()[0] == QChar(0x3C) || include.trimmed()[0] == QChar(0x22) ) final_include += "#include "+include.trimmed()+"\n"; // < or "
                else final_include += "#include <"+include.trimmed()+">\n"; //

            }

            intrinsicTemplate.replace("%%INSTRUCTION_INCLUDE%%", final_include);
        }

        intrinsicTemplate.replace("%%OP_INIT%%", parameterInit(i, &usedParameters));
        intrinsicTemplate.replace("%%LOOP_SIZE%%", QString::number( m_job->loopSize()) );
        intrinsicTemplate.replace("%%INTRINSIC_COMMAND%%", buildIntrinsicCommand());

        emit templateFinished(intrinsicTemplate, usedParameters);
    }
    emit codeGenFinished();
}
