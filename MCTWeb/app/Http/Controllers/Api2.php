<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

#MySQL
use App\Models\Intrinsic;
use App\Models\IntrinsicParameter;

use App\Models\Node;
use App\Models\Error;
use App\Models\Template;
use App\Models\ParameterType;
use App\Models\ParameterTypeInit;
use App\Models\ParameterTypeInitCategory;

# MongoDB
use App\Models\Job;
use App\Models\Result;


class Api2 extends Controller
{
    public function todo()
    {
        $return["successful"] = false;
        $return["error"] = 1;
        $return["error_message"] = "not yet implemented";
        return $return;
    }
    
    public function getDoku()
    {
        $routes_file = fopen(base_path()."/app/Http/routes.php", "r") or die("Unable to open file!");
        $routes_file_content = fread($routes_file,filesize(base_path()."/app/Http/routes.php"));
        fclose($routes_file);
        
        $routes_file_content_lines = explode("\n",$routes_file_content);
       
        $return = array();
        $api = "";
        $between = false;
        for($i = 0; $i<count($routes_file_content_lines); $i++){
            if(strpos($routes_file_content_lines[$i], 'START') !== false || $between){
                $between = true;
                $api .= $routes_file_content_lines[$i]."\n";
                if(strpos($routes_file_content_lines[$i], 'END') !== false) $between = false;
            }

        }
        
        return view('api', [
                            'api' => $api,
                            'include' => 'api.blade.php',
                            ]);        
    }
    
    public function getInstruction($instruction_id)
    {
        return Intrinsic::find($instruction_id);
    }
    public function getInstructions()
    {
        return Intrinsic::all();        
    }
    
    public function saveInstruction($instruction_id){
        $return = array();
        
        $instruction = Intrinsic::find($instruction_id);
        
        if($instruction){
            $instruction->saved ? $instruction->saved = 0 : $instruction->saved = 1;
            $instruction->save();
            
            $return["success"] = true;
            $return["state"] = $instruction->saved;
        }
        else{
            $return["success"] = false;
        }
        
        return $return;
    }
    
    public function setInstructionTemplate($instruction_id, $template_id){
        $return = array();
        
        $instruction = Intrinsic::find($instruction_id);
        if(!$instruction){
            $return["success"] = false;
            $return["message"] = "No such Instruction";
            return $return;
        }
        
        $template = Template::find($template_id);
        if(!$template){
            $return["success"] = false;
            $return["message"] = "No such Template";
            return $return;
        }
        
        $instruction->template_id = $template_id;
        $instruction->save();
        
        $return["success"] = true;
        
        return $return;
        
    }
    
    public function getResult($result_id)
    {
        return Result::where('_id', $result_id)->first();
    }
    
    public function getResults($instruction_id, $job_id, $node_id)
    {
        return  Result::where('job_id', $job_id)->where('instruction_id', intval($instruction_id))->where('node_id', $node_id)->orderBy('_id', 'asc')->get();
    }    
    public function getResultsHide($instruction_id, $job_id, $node_id, $hide_csv){
    
        $hide = explode(',', $hide_csv);
    
        return  Result::where('job_id', $job_id)->where('instruction_id', intval($instruction_id))->where('node_id', $node_id)->orderBy('_id', 'asc')->get()->each(
        
            function($row) use ($hide) {
                $row->setHidden($hide);
            }
        
        );
        
    }
    
    public function getResultsHidePaginate($instruction_id, $job_id, $node_id, $hide_csv, $pageSize){
    
        $hide = explode(',', $hide_csv);
    
        return  Result::where('job_id', $job_id)->where('instruction_id', intval($instruction_id))->where('node_id', $node_id)->orderBy('_id', 'asc')->simplePaginate($pageSize)->each(
        
            function($row) use ($hide) {
                $row->setHidden($hide);
            }
        
        );
        
    }    
    
    public function getJob($job_id)
    {      
        return Job::where('id', $job_id)->first();
    }      
    
    public function changeJob($job_id, Request $request)
    {      
        $job = Job::where('id', $job_id)->first();
        $job->comment = $request->input('comment');
        $job->save();
    }   
    
    public function changeJobFlag($job_id, Request $request)
    {    
        $return = array();
        $job = Job::where('id', $job_id)->first();

        $flag_list = ["is_mctbench", "is_lownoise", "is_test", "is_asm"];
        $flag = $request->input('flag');
        if(in_array($flag, $flag_list)){
        
            $new = array();
            $new[$flag] = !$job->flags[$flag];
            
            $job->flags = array_merge($job->flags, $new);
            $job->save();

            $return["success"] = true;
            $return["job_id"] = $job_id;
            $return["flag"] = $request->input('flag');
            $return["flag_value"] = $job->flags[$flag];
        
        }
        else $return["success"] = false;
        
        return $return;
    }  
    
    public function getJobs()
    {      
        return Job::all();
    }
    
    public function deleteJob($job_id)
    {   
        $return = array();
        
        $job = Job::where('id', $job_id)->first();
        if($job){
            Result::where('job_id', $job_id)->delete();
            Error::where('job_id', $job_id)->delete();
            $return["successful"] = true;
        }
        else{ 
            $return["successful"] = false;
            $return["error_message"] = "no such job";
        }
        $job->delete();
        
        return $return;
    }
    
    public function deleteNodeFromJob($node_id, $job_id){
        $return = array();
        $job = Job::where('id', $job_id)->first();
        if($job){
            // Check if Node is in Job        
            if(in_array($node_id, $job->node_ids)){
                
                
                // Remove Node from job_id
                $node_ids = $job->node_ids;
                if (($key = array_search($node_id, $node_ids)) !== false) {
                    unset($node_ids[$key]);
                }
                
                if(empty($node_ids)){
                    $return["message"] = "Node list is empty. Deleting Job too.";
                    $this->deleteJob($job_id);
                }
                else{
                
                    Result::where('job_id', $job_id)->delete();
                    Error::where('job_id', $job_id)->delete();
                
                    $job->node_ids = $node_ids;
                    $job->save();
                }
                
                
                $return["success"] = true;
                $return["job_id"] = $job_id;
                $return["node_id"] = $node_id;
            }
            else{
                $return["success"] = false;
                $return["error_message"] = "no such node in job";
            }
        }
        else{
            $return["success"] = false;
            $return["error_message"] = "no such job";
        }
        
        return $return;
    }
    
    public function getNode($node_id)
    {      
        return Node::where('identifier', $node_id)->first();
    } 
    
    public function getNodes()
    {      
        return Node::all();
    } 

    public function getTemplate($template_id)
    {      
        return Template::where('id', $template_id)->first();
    } 
    
    public function getTemplates()
    {      
        return Template::all();
    }   
    
    public function changeTemplate($template_id, Request $request)
    {
        $template = Template::find($template_id);
        $template->name = $request->input("name", "not set");
        $template->template = $request->input("template", "not set");
        $template->save();
        
        $ret = array();
        $ret["success"] = true;
        return $ret;
    }
    
    public function deleteTemplate($template_id)
    {
        $template = Template::find($template_id);
        
        $template->delete();
        
        $ret = array();
        $ret["success"] = true;
        return $ret;
    }    

    public function getParameterCompleteInit($parameter_id)
    {      
        return ParameterCompleteInit::where('id', $parameter_id)->first();
    } 
    
    public function getParameterCompleteInits()
    {      
        return ParameterCompleteInit::all();
    } 
    
    public function parameterTypeInitCategoryAdd($cat_name)
    {
        //$newCatName = $request->input('catName');
        $newCat = new ParameterTypeInitCategory();
        $newCat->name = $cat_name;
        $newCat->save();

        $test = array();
        $test['name'] = $newCat->name;
        $test['id'] = $newCat->id;
        $test['success'] = true;
        return $test;
    }
    public function submitParameterCompleteInit(Request $request)
    {
        // Code already exists in IntrinsicController. TODO: Merge
        $return = array();
        $newCompleteInit = $request->input();
        if(!ParameterType::findOrFail($newCompleteInit["parameter_type_id"])){
            
            $return["success"] = false;
            $return["message"] = "ParameterType not found";
        }
        else{
            $pti = new ParameterTypeInit;
            $pti["parameter_type_id"] = $newCompleteInit["parameter_type_id"];
            $pti["description"] = $newCompleteInit["description"];
            $pti["code"] = $newCompleteInit["code"];
            $pti["precode"] = $newCompleteInit["precode"];
            $pti["enabled"] = 1;
            $pti->save();
            $pti->categories()->sync($newCompleteInit['categories']);
            
            $return["success"] = true;
        } 
        
        return $return;
    }
    
    public function instructionParameterTypeInitModify($p_id, $pti_id, $action){
        
        $ip = IntrinsicParameter::find($p_id);
        if($ip === NULL){
            $return["success"] = false;
            $return["message"] = "Could not find IntrinsicParameter with id ".$p_id;
            
            return $return;
        }
        
        $pti = ParameterTypeInit::find($pti_id);
        if($pti === NULL){
            $return["success"] = false;
            $return["message"] = "Could not find ParameterTypeInit with id ".$pti_id;
            
            return $return;
        }
        
        if($ip->parameter_type_id != $pti->parameter_type_id){
            $return["success"] = false;
            $return["message"] = "Wrong ParameterTypeInit for this IntrinsicParameter";
            
            return $return;
        } 

        switch($action){
        case"toggle":
            $ip->activeInits()->toggle($pti_id);
        break;
        
        case"enable":
            $ip->activeInits()->syncWithoutDetaching([$pti_id]);
        break;
        
        case"disable":
            $ip->activeInits()->detach($pti_id);
        break;
        
        default:
            $return["success"] = false;
            $return["message"] = "Unknown action";
            return $return;
        break;        
        }
        $return["success"] = true;
        return $return; 
    }
    
    public function instructionParameterTypeInitModifyRange($p_id, $pti_id_start, $pti_id_end, $action){
        $ret = array();
        for($i = $pti_id_start; $i <= $pti_id_end; $i++){
            $ret[] = $this->instructionParameterTypeInitModify($p_id, $i, $action);
        }
        return $ret;
    }
    
    public function instructionParameterTypeInitModifyBulk($p_id, $action){
        
  
        $ip = IntrinsicParameter::find($p_id);
        if($ip === NULL){
            $return["success"] = false;
            $return["message"] = "Could not find IntrinsicParameter with id ".$p_id;
            
            return $return;
        }
        
        
        switch($action){
        
        case"enable":
        
            //Getting all ParameterTypeInits for IntrinsicParameter
            $inits = $ip->inits();

            $init_ids = array();
            foreach($inits as $init) $init_ids[] = $init->id;        
            $ip->activeInits()->sync($init_ids);
        break;
        
        case"disable":
            $ip->activeInits()->detach();
        break;
        
        default:
            $return["success"] = false;
            $return["message"] = "Unknown action";
            return $return;
        break;
        }
        $return["success"] = true;
        return $return;
            
    }
    
    public function instructionParameterTypeInitList($p_id, $action, $page_size=100){
        
  
        $ip = IntrinsicParameter::find($p_id);
        if($ip === NULL){
            $return["success"] = false;
            $return["message"] = "Could not find IntrinsicParameter with id ".$p_id;
            
            return $return;
        }
        
        switch($action){
        
        case"active":
        
            //Getting all ParameterTypeInits for IntrinsicParameter
            return $ip->activeInits()->simplePaginate($page_size);
        break;
        
        case"inactive":
            return $ip->inactiveInits2()->simplePaginate($page_size);
        break;
        
        case"all":
            return $ip->inits2()->simplePaginate($page_size);
        break;
        
        default:
            $return["success"] = false;
            $return["message"] = "Unknown action";
            return $return;
        break;
        }
        $return["success"] = true;
        return $return;
            
    } 
        
}

?> 
