<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
/*
$app->get('/', function () use ($app) {
    return $app->welcome();

});*/

$app->get('/', 'IntrinsicController@showOverview');
$app->get('overview', 'IntrinsicController@showOverview');

// Todo: rename intrinsic to instruction
$app->get('intrinsics/list/{instruction_id}', 'IntrinsicController@showIntrinsic');
$app->get('intrinsics/list/{instruction_id}/job/{job_id}', 'IntrinsicController@showIntrinsic');
$app->get('intrinsics/list/{instruction_id}/job/{job_id}/node/{node_id}', 'IntrinsicController@showIntrinsic');

$app->get('intrinsics/list/saved/{instruction_id}', 'IntrinsicController@showIntrinsic');
$app->get('intrinsics', 'IntrinsicController@showIntrinsics');
$app->get('intrinsics/category/{category}', 'IntrinsicController@showIntrinsicsOfCategory');
$app->get('intrinsics/category/', 'IntrinsicController@showIntrinsicCategories');

$app->get('intrinsics/add', 'IntrinsicController@showIntrinsicAdd');
$app->post('intrinsics/add', 'IntrinsicController@saveIntrinsicAdd');

$app->post('intrinsics/{id}/updateInitParams', 'IntrinsicController@updateInitParams');
$app->post('intrinsics/{id}/updateInitParamCategories', 'IntrinsicController@updateInitParamCategories');

$app->get('jobs/', 'IntrinsicController@showJobs');
$app->get('jobs/{id}', 'IntrinsicController@showJobs');
$app->post('jobs/submit', 'AmpqController@queueJobDirectly');
$app->get('job/{id}/delete', 'IntrinsicController@_deleteJob');
$app->post('job/{job_id}/change', 'JobController@changeJobComment');
$app->post('job/{job_id}/change/redirect/{redirect_to}', 'JobController@changeJobComment');

$app->get('templates/', 'IntrinsicController@showTemplates');
$app->get('template/{template_id}', 'IntrinsicController@showTemplates');

$app->get('result/{result_id}', 'IntrinsicController@showResult');

$app->get('nodes/list/', 'IntrinsicController@showNodes');
$app->get('nodes/list/{node_id}', 'IntrinsicController@showNodes');

$app->get('nodes/add', 'IntrinsicController@showNodeAdd');
$app->post('nodes/add', 'IntrinsicController@showNodeAddSave');

$app->get('parameterTypeInits/list', 'IntrinsicController@showParameterTypeInits');
$app->get('parameterTypeInits/list/{pt_id}', 'IntrinsicController@showParameterTypeInits');
$app->get('parameterTypeInits/{pt_id}/add', 'IntrinsicController@showParameterTypeInitAdd');
$app->post('parameterTypeInits/{pt_id}/add', 'IntrinsicController@saveParameterTypeInitAdd');

$app->get('parameterTypeInits/{pti_id}/edit', 'IntrinsicController@showParameterTypeInitEdit');
$app->post('parameterTypeInits/{pti_id}/edit', 'IntrinsicController@saveParameterTypeInitEdit');

$app->get('parameterTypeInits/{pti_id}/delete', 'IntrinsicController@showParameterTypeInitDelete');

$app->post('parameterTypeInits/manage', 'IntrinsicController@saveParameterTypeInitManage');

// Categories
$app->get('parameterTypeInits/categories/list', 'IntrinsicController@showParameterTypeInitCategories');
$app->get('parameterTypeInits/categories/makeDefault/{id}', 'IntrinsicController@saveParameterTypeInitCategoriesDefault');
$app->get('parameterTypeInits/categories/delete/{id}', 'IntrinsicController@saveParameterTypeInitCategoriesDelete');

//Errors
$app->get('errors/list', 'IntrinsicController@showErrors');
$app->get('errors/list/{job_id}/{node_id}', 'IntrinsicController@showErrors');

// Math
$app->get('variance/job/{job_id}', 'IntrinsicController@showVariance');


/* AMPQ */
$app->get('ampq/prepareJob', 'AmpqController@prepareJob');
$app->get('ampq/prepareJob/{instruction_id}', 'AmpqController@prepareJob');

$app->post('ampq/queueJob', 'AmpqController@queueJob');

$app->get('ampq/queueStatus', 'AmpqController@queueStatus');
$app->get('ampq/purgeQueue/{name}', 'AmpqController@purgeQueue');

//Test
$app->get('test', 'IntrinsicController@test');
$app->get('intrinsic2asm', 'IntrinsicController@intrinsic2asm');

/* API v2 START*/
$app->get('api2', 'Api2@getDoku');

$app->get('api2/instructions', 'Api2@getInstructions');
$app->get('api2/instruction/{instruction_id}', 'Api2@getInstruction');
$app->get('api2/instruction/{instruction_id}/save', 'Api2@saveInstruction');
$app->get('api2/instruction/{instruction_id}/template/{template_id}', 'Api2@setInstructionTemplate');

$app->get('api2/jobs', 'Api2@getJobs');
$app->get('api2/job/{job_id}', 'Api2@getJob');
$app->get('api2/job/{job_id}/delete', 'Api2@deleteJob');
$app->get('api2/job/{job_id}/removeNode/{node_id}', 'Api2@deleteNodeFromJob');
$app->post('api2/job/{job_id}/change', 'Api2@changeJobComment');
$app->post('api2/job/{job_id}/changeFlag', 'Api2@changeJobFlag');
$app->post('api2/job/submit', 'AmpqController@queueJobDirectly');

$app->get('api2/node/{node_id}', 'Api2@getNode');
$app->get('api2/nodes', 'Api2@getNodes');

$app->get('api2/template/{template_id}', 'Api2@getTemplate');
$app->post('api2/template/change/{template_id}', 'Api2@changeTemplate');
$app->post('api2/template/delete/{template_id}', 'Api2@deleteTemplate');
$app->get('api2/templates', 'Api2@getTemplates');

$app->get('api2/parameterCompleteInit/{parameter_id}', 'Api2@getParameterCompleteInit');
$app->post('api2/parameterCompleteInit/submit', 'Api2@submitParameterCompleteInit');
$app->get('api2/parameterCompleteInits', 'Api2@getParameterCompleteInits');

$app->get('api2/result/{result_id}', 'Api2@getResult');

//MCTBench
$app->post('mctbench/submit', 'MCTBench@submitResults');
$app->post('mctbench/generateCode', 'MCTBench@generateCode');
$app->get('mctbench/update', 'MCTBench@currentVersion');

// Custom queries
$app->get('api2/instruction/{instruction_id}/job/{job_id}/node/{node_id}', 'Api2@getResults');

$app->get('api2/instruction/{instruction_id}/job/{job_id}/node/{node_id}/hide/{hide_csv}', 'Api2@getResultsHide');
// e.g.: hide_csv = asm,results,parameters
$app->get('api2/instruction/{instruction_id}/job/{job_id}/node/{node_id}/hide/{hide_csv}/pageSize/{page_size}', 'Api2@getResultsHidePaginate');


$app->get('api2/parameterTypeInitModify/{p_id}/{pti_id_start}/{pti_id_end}/{action}', 'Api2@instructionParameterTypeInitModifyRange');
$app->get('api2/parameterTypeInitModify/{p_id}/{pti_id}/{action}', 'Api2@instructionParameterTypeInitModify');

$app->get('api2/parameterTypeInitModify/{p_id}/{action}', 'Api2@instructionParameterTypeInitModifyBulk');
$app->get('api2/parameterTypeInitList/{p_id}/{action}/pageSize/{page_size}', 'Api2@instructionParameterTypeInitList');

$app->get('api2/parameterTypeInitCategory/add/{cat_name}', 'Api2@parameterTypeInitCategoryAdd');

// Calc
$app->get('api2/calc/variance/job/{job_id}', 'Math@calcVariance');

/* API v2 END*/


/* Highcharts */

$app->get('highcharts/instruction/{instruction_id}/job/{job_id}/node/{node_id}', 'Highcharts@hcResults');
$app->get('highcharts/result/{result_id}', 'Highcharts@hcResult');

