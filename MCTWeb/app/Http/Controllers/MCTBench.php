<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

#MySQL
use App\Models\Intrinsic;
use App\Models\Category;
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
use App\Models\MctBenchResult;


class MCTBench extends Controller
{
    private $m_initCombinationsList = array();
    private $m_parametersInitList = array();
    private $m_tmpOperator = array();
    private $m_cCode = array();

    public function submitResults(Request $request){
        $newMCTBenchResult = new MCTBenchResult;
        $newMCTBenchResult->result = $request->all();
        
        $ret = array();
        if($newMCTBenchResult->save()) $ret["success"] = True;
        else $ret["success"] = False;
        
        return $ret;
    }
    
    public function currentVersion(){
        $ret = array();
        $ret["version"] = 0;
        $ret["url"] = "http://192.168.56.102/updates/test";
        
        return $ret;
    }
    
    public function generateCode(Request $request){

        //return $request->all();
        $instructions = $request->all()["instructions"];
        $builtInstructions = array();
        $loopsize = $request->all()["loopsize"];
        
        /*$this->m_cCode[0]  = "int loopSize = ".$request->all()["loopsize"].";";
        $this->m_cCode[0] .= "QVector<quint64> results(loopSize);";
        $this->m_cCode[0] .= "quint64 *pResults = results.data();";*/
        
        $this->m_cCode[] .= "void GCCBenchmark::startREPLACEME(){\n";
        $this->m_cCode[] .= "int i = 0;\n";
        
        $ret = array();
        foreach($instructions as $instruction){
            // Category?
            if(isset($instruction["parameter_category"]))
            {
                if(isset($instruction["instruction_category"])){
                    if($instruction["msvc"]) $instructionsOfCategory =  Category::find($instruction["instruction_category"])->instructions->where('msvc', 1);
                    else $instructionsOfCategory =  Category::find($instruction["instruction_category"])->instructions;
                }
                else{
                    $instructionsOfCategory = Intrinsic::where('id', $instruction["instruction_id"])->get();
                }
                foreach($instructionsOfCategory as $instructionObj){
                    if($instructionObj->active){
                        //$instructionObj = Intrinsic::find($instruction["instruction_id"]);
                        $cat_id = $instruction["parameter_category"];
                        
                        // Get inits of category for each parameter
                        $paramTypeInitCategory = ParameterTypeInitCategory::find($cat_id);
                        $countParams = 0;
                        foreach($instructionObj->parameters as $parameter){
                            $this->m_parametersInitList[] = $paramTypeInitCategory->parameterTypeInitsOfType($parameter->type->id)->get();
                            $countParams++;
                        }
                        $this->buildInitCombinationsRecursive(0, $countParams);
                        //return $this->m_initCombinationsList;
                        
                        foreach($this->m_initCombinationsList as $initcombination){
                            $data["instruction_id"] = $instructionObj->id;
                            $data["parameters"] = array();
                            foreach($initcombination as $initcombinationParameter){
                                $data["parameters"][] = $initcombinationParameter["id"];
                            }
                            //$builtInstructions[] = $this->buildInitAndInstruction($data);
                            $this->m_cCode[] = $this->generateCCode($this->buildInitAndInstruction($data), $loopsize, 1);
                        }
                        
                        $this->m_initCombinationsList = array();
                        $this->m_parametersInitList = array();
                        $this->m_tmpOperator = array();
                    }
                }
                
            }
            else {
                
                $data = array();
                $data["instruction_id"] = $instruction["instruction_id"];
                foreach($instruction["parameters"] as $parameter){
                    $data["parameters"] = $parameter;
                    
                    //$builtInstructions[] =  $this->buildInitAndInstruction($data);
                    $this->m_cCode[] = $this->generateCCode($this->buildInitAndInstruction($data), $loopsize, 1);
                }
            }
            
        }
        $this->m_cCode[] = "}";
        return $this->m_cCode;
    }
    
    private function buildInitCombinationsRecursive($depth, $max_depth){
        //if($depth == count($this->m_parametersInitList)){
        if($depth == $max_depth){
            $this->m_initCombinationsList[] = $this->m_tmpOperator;
            return;
        }
        
        for($i = 0; $i < count($this->m_parametersInitList[$depth]); $i++){
            $this->m_tmpOperator[] = $this->m_parametersInitList[$depth][$i];
            $this->buildInitCombinationsRecursive($depth+1, $max_depth);
            array_pop($this->m_tmpOperator);
        }
    }
    
    private function generateCCode($data, $loopsize ,$repetitions){
        $code = "{\n";
        
        foreach($data["inits"] as $init) $code .= $init."\n";
        
        $code .= "RDTSC_WARMUP\n";
        $code .= "for(i = 0; i<m_loopSize; i++){\n";
        $code .= "\tRDTSC_START\n";
        $code .= "\t".$data["instruction"]."\n";
        $code .= "\tRDTSC_END\n";
        $code .= "\tSAVE_RESULTS_IN_VECTOR\n";        
        $code .= "}\n";
        
        $code .= "saveResultsInList(m_resultVector, ".$data["instruction_id"].", QList<quint64>()";
        foreach($data["init_ids"] as $init_id) $code .= " << ".$init_id;
        $code .= ");\n";
        
        $code .= "}\n\n";
        
        
        return $code;
    }
    
    
    private function buildInitAndInstruction($data){
        $instructionObj = Intrinsic::find($data["instruction_id"]);
        $c_inits = array();
        $c_inits_immediate = array();
        $c_instruction;
        $parameterNames = array();
        
        $i = 0;
        foreach($instructionObj->parameters as $parameter){
            
            $parameterNames[$i] = $parameter["name"];
            if($parameter["immediate"]) $parameterNames[$i] = "";
            $i++;
        }
        
        
        $i = 0;
        foreach($data["parameters"] as $parameter_id){
            
            $pti = ParameterTypeInit::find($parameter_id);
            if($parameterNames[$i] == "") {
                $c_inits_immediate[$i] = $pti->code; //Immediate value
                $c_inits[$i] = "";
            }
            else{
                $c_inits[$i] = $pti->type->type." ".$parameterNames[$i] ." = ". $pti->code.";" ;
                $c_inits_immediate[$i] = "";
            }
            $i++;
        
        }
        $c_instruction = "";
        $c_instruction .= $instructionObj->precode ."\n";
        if($instructionObj->returnParameter->type != "void") $c_instruction .= $instructionObj->returnParameter->type." ret = ";
        $c_instruction .= $instructionObj->intrinsic."(";
        $countParameters = count($parameterNames);
        $i = 0;
        foreach($parameterNames as $parameterName){
            if($parameterName == "")  $c_instruction .= $c_inits_immediate[$i];
            else $c_instruction .= $parameterName;
            if($i != $countParameters-1) $c_instruction .= ", ";
            $i++;
        }
        $c_instruction .= ");";
        
        $return["instruction"] = $c_instruction;
        $return["instruction_id"] = $data["instruction_id"];
        $return["inits"] = $c_inits;
        $return["init_ids"] = $data["parameters"];
            
        return $return;
    }
}

?> 
