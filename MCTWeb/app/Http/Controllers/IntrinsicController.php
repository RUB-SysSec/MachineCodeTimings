<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
# MySQL
use App\Models\Intrinsic;
use App\Models\IntrinsicParameter;
use App\Models\Measurement;
use App\Models\Category;
use App\Models\Node;

use App\Models\Template;
use App\Models\ParameterType;
use App\Models\ParameterTypeInit;
use App\Models\ParameterTypeInitCategory;

use App\Models\Compilation;
use App\Models\CompilationEntry;

# MongoDB
use App\Models\Job;
use App\Models\Error;

use App\Http\Controllers\Math;

class IntrinsicController extends Api2
{


    public function showOverview()
    {
        //return "controller intrinsic: ".$id;
        return view('overview', ['include' => 'overview.blade.php',
                                 'jobs' => Job::orderBy('creation_time', 'desc')->get()
                                ]);
    }

    
    // Intrinsics
    public function showIntrinsicOld($instruction_id, $job_id = -1, $node_id = -1,  Request $request)
    {
        $intrinsic = Intrinsic::find($instruction_id);
        if($job_id == -1) $jobs = Job::where('instruction_ids', intval($instruction_id))->orderBy('creation_time', 'desc')->paginate(10);
        else $jobs = Job::where('id', $job_id)->where('instruction_ids', intval($instruction_id))->orderBy('creation_time', 'desc')->paginate(10);


        if($node_id == -1) $nodes = Node::all();
        else $nodes = Node::where('identifier', $node_id)->get();
        
        
/*   
        $errors = Error::where('instruction_id', intval($instruction_id))->get();
        
        $errorMap = array();
        foreach($errors as $error){
            $errorMap[$error->job_id][$error->node_id] = true;
        }
*/
        // Saved Stuff
        $sql_saved = "";
        if ($request->is('intrinsic/saved*')) {
            $sql_saved = "AND saved='1'";
        }

        // Next
        $next_intrinsic_result = \DB::select("SELECT id FROM intrinsics WHERE id = (SELECT min(id) FROM intrinsics WHERE id > ".$instruction_id." AND active='1' ".$sql_saved.")");
        if(count($next_intrinsic_result) > 0) $next_intrinsic_id = $next_intrinsic_result[0]->id;
        else $next_intrinsic_id = -1;
        $next_intrinsic = Intrinsic::find($next_intrinsic_id);
        
        // Prev
        $prev_intrinsic_result = \DB::select("SELECT id FROM intrinsics WHERE id = (SELECT max(id) FROM intrinsics WHERE id < ".$instruction_id." AND active='1' ".$sql_saved.")");
        $prev_intrinsic_id = -1;
        if(count($prev_intrinsic_result) > 0){
                $prev_intrinsic_id = $prev_intrinsic_result[0]->id;
        }
        $prev_intrinsic = Intrinsic::find($prev_intrinsic_id);
        //

        
                                    
        return view('intrinsicOld', [
                                    'id' => $instruction_id,
                                    'intrinsic' => $intrinsic,
                                    'parameterTypeInitCategories' => ParameterTypeInitCategory::all(),
                                    'jobs' => $jobs,
                                    'templates' => Template::all(),
                                    //'errorMap' => $errorMap,
                                    'nodes' => $nodes,
                                    'type' => 'default',     // TODO   
                                    'intrinsic_next' => $next_intrinsic,
                                    'intrinsic_prev' => $prev_intrinsic,        
                                    'include' => 'intrinsic.blade.php'
                                    ]);                                 
    }
    
    public function showIntrinsic($instruction_id, $job_id = -1, $node_id = -1)
    {       
        $instruction = Intrinsic::find($instruction_id);
        // get next Intrinsic id
        $intrinsic_next = Intrinsic::where('id', '>', $instruction_id)->orderBy('id', 'asc')->first();
        
        // get previous Intrinsic id
        $intrinsic_prev = Intrinsic::where('id', '<', $instruction_id)->orderBy('id', 'desc')->first();
        
        $job_ids = array();
        $node_ids = array();
        
        if($job_id != -1) $job_ids[] = $job_id;
        if($node_id != -1) $node_ids[] = $node_id;

        return view('intrinsic', [
                                    'parameterTypeInitCategories' => ParameterTypeInitCategory::all(),
                                    'intrinsic' => $instruction,
                                    'templates' => Template::all(),
                                    'include' => 'intrinsicNew.blade.php',
                                    'intrinsic_next' => $intrinsic_next,
                                    'intrinsic_prev' => $intrinsic_prev,
                                    'job_ids' => $job_ids,
                                    'node_ids' => $node_ids
                                    ]);                                 
    }    
    
    public function showResult($result_id)
    {
       
        $result = $this->getResult($result_id);
                                 
        return view('result', [
                                 'include' => 'result.blade.php',
                                 'result' => $result
                                 ]);
    }    
        
    public function showIntrinsics()
    {

        $results = Intrinsic::all();
        return view('intrinsics', ['intrinsics' => $results, 'include' => 'intrinsics.blade.php']);
    }

    public function showIntrinsicAdd()
    {
        $cats = Category::all();
        return view('intrinsicAdd', ['categories' => $cats, 'paraTypes' => ParameterType::all(), 'include' => 'intrinsicAdd.blade.php']);
    }
    
    public function saveIntrinsicAdd(Request $request)
    {
        
        // Add Intrinsic
        $intrinsic = new Intrinsic;
        $intrinsic->precode = $request->input("precode");
        $intrinsic->intrinsic = $request->input("instruction");
        $intrinsic->category_id = $request->input("category");
        $intrinsic->rettype = $request->input("rettype");
        $intrinsic->compiler_command = $request->input("compiler_command");
        $intrinsic->include = $request->input("include_headers", "");
        $intrinsic->save();
        
        if(!empty($request->input("parameters")))
        {
            $parameters = explode(",", $request->input("parameters"));
            
            foreach($parameters as $parameter){

                $type = explode(" ", trim($parameter));
                $name = $type[count($type)-1];
                $type = substr(
                               trim($parameter), 
                               0,
                               strlen(trim($parameter)) - strlen($name) 
                );
                $parameterType = ParameterType::where("type", $type)->get()->first();
                
                if(count($parameterType)==0){ 
                    // Create
                    $parameterType = new ParameterType;
                    $parameterType->type = $type;
                    $parameterType->save();                
                }
                
                $intrinsicParameter = new IntrinsicParameter();
                $intrinsicParameter->intrinsic_id = $intrinsic->id;
                $intrinsicParameter->parameter_type_id = $parameterType->id;
                $intrinsicParameter->name = $name;
                $intrinsicParameter->save();            
            }
        }
        
        return redirect('intrinsics/list/'.$intrinsic->id);

    }  
    

    public function showNodes($node_id = -1)
    {
        if($node_id != -1) $nodes = Node::where('identifier', $node_id)->get();
        else $nodes = Node::all();
        return view('node', ['nodes' => $nodes, 'include' => 'node.blade.php']);
    }
    
    public function showNodeAdd($node_id = -1)
    {
        return view('nodeAdd', ['include' => 'nodeAdd.blade.php']);
    }    
    
    public function showNodeAddSave(Request $request)
    {      
        $node = new Node;
        $node->identifier = $request->input("identifier");
        
        $node->name = $request->input("name");
        $node->save();
        
        return redirect('nodes/list/'.$node->identifier);
    }       
        
    public function showJobs($id = -1)
    {
        if($id == -1) $jobs = Job::orderBy('creation_time', 'DESC')->paginate(10);
        else $jobs = Job::where('id', $id)->get();
        return view('jobs', 
                    ['jobs' => $jobs, 
                     'include' => 'jobs.blade.php'
                    ]);
    }   

    public function _deleteJob($id)
    {

        $return = $this->deleteJob($id);
        if($return['successful']) return redirect('jobs/');
        else return $return;
    }    
    
    public function showTemplates($template_id = -1)
    {

        if($template_id == -1) $templates = Template::orderBy('id', 'DESC')->get();
        else $templates = Template::where('id', $template_id)->get();
        
        return view('templates', 
                    ['templates' => $templates, 
                     'include' => 'templates.blade.php'
                    ]);
    }

    public function showParameterTypeInits($pt_id = -1)
    {
        if($pt_id == -1) $parameterTypes = ParameterType::all();
        else $parameterTypes = ParameterType::find([$pt_id]);

        return view('parameterTypeInits', 
                    [
                    'include' => 'parameterTypeInit.blade.php',
                    'parameterTypes' => $parameterTypes
                    ]);
    }         

    public function showParameterTypeInitAdd($pt_id)
    {

        return view('parameterTypeInitAdd_Edit', 
                    [
                    'include' => 'parameterTypeInitAdd_Edit.blade.php',
                    'categories' => ParameterTypeInitCategory::all(),
                    'parameterType' => ParameterType::find($pt_id)
                    ]);
    }

    public function saveParameterTypeInitAdd($pt_id, Request $request)
    {
        if($request->input("edit")) $new_parameterTypeInit = ParameterTypeInit::find($pti_id);
        else $new_parameterTypeInit = new ParameterTypeInit;
        $new_parameterTypeInit->parameter_type_id = $pt_id;
        $new_parameterTypeInit->enabled = 1;
        $new_parameterTypeInit->code = $request->input("code");
        $new_parameterTypeInit->precode = $request->input("precode");
        $new_parameterTypeInit->description = $request->input("description");
        $new_parameterTypeInit->save();

        $new_parameterTypeInit->categories()->attach($request->input('categories'));
        

        return redirect('parameterTypeInits/'.$pt_id);

        /*return view('parameterTypeInitAdd', 
                    [
                    'include' => 'parameterTypeInitAdd.blade.php',
                    'categories' => ParameterTypeInitCategory::all(),
                    'parameterType' => ParameterType::find($pti_id)
                    ]);*/
    }

    public function showParameterTypeInitEdit($pti_id)
    {
        $pti = ParameterTypeInit::find($pti_id);
        return view('parameterTypeInitAdd_Edit', 
                    [
                    'include' => 'parameterTypeInitEdit.blade.php',

                    'categories' => ParameterTypeInitCategory::all()->keyBy("name"),
                    'parameterType' => $pti->type,
                    'parameterTypeInit' => $pti
                    ]);
    }     

    public function saveParameterTypeInitEdit($pti_id, Request $request)
    {
        $new_parameterTypeInit = ParameterTypeInit::find($pti_id);
        $new_parameterTypeInit->enabled = 1;
        $new_parameterTypeInit->code = $request->input("code");
        $new_parameterTypeInit->precode = $request->input("precode");
        $new_parameterTypeInit->description = $request->input("description");
        $new_parameterTypeInit->save();

        return redirect('parameterTypeInits/'.$new_parameterTypeInit->parameter_type_id);
    }   

    public function showParameterTypeInitDelete($pti_id, Request $request)
    {
        $new_parameterTypeInit = ParameterTypeInit::find($pti_id);
        $type_id = $new_parameterTypeInit->parameter_type_id;
        $new_parameterTypeInit->categories()->detach();
        $new_parameterTypeInit->intrinsicParameters()->detach();
        $new_parameterTypeInit->delete();

        return redirect('parameterTypeInits/list/'.$type_id);
    }        


    public function saveParameterTypeInitManage(Request $request){

        
        if(empty($request->input("parameterTypeInitId"))){
            // Create new
            $new_parameterTypeInit = new ParameterTypeInit;
        }
        else{
            //Edit
            $new_parameterTypeInit = ParameterTypeInit::find($request->input("parameterTypeInitId"));
        }
        
        $new_parameterTypeInit->parameter_type_id = $request->input("parameterTypeId");
        $new_parameterTypeInit->enabled = 1;
        $new_parameterTypeInit->code = $request->input("code");
        $new_parameterTypeInit->precode = $request->input("precode");
        $new_parameterTypeInit->description = $request->input("description");
        $new_parameterTypeInit->save();
        $new_parameterTypeInit->categories()->sync($request->input('categories', array()));
        
        
        return redirect('parameterTypeInits/list/'.$request->input("parameterTypeId"));        
        

    }
    
    public function showIntrinsicsOfCategory($category_id)
    {

        $results = Intrinsic::where('category_id', $category_id)->get();
        return view('intrinsics', ['intrinsics' => $results, 'include' => 'intrinsics.blade.php']);
    }


    
    public function showIntrinsicCategories()
    {

        $results = Category::all();
        return view('intrinsic_categories', ['categories' => $results, 'include' => 'intrinsic_categories.blade.php']);
    }

    public function updateInitParams($id, Request $request){
        $intrinsic = Intrinsic::find($id);
        foreach($intrinsic->parameters as $parameter){
            /*echo $parameter->name."\nactive:\n";
            print_r($request->input($parameter->id.'_active'));
            echo "inactive\n";
            print_r($request->input($parameter->id.'_inactive'));*/
            
            if($request->has($parameter->id.'_active')) $parameter->activeInits()->detach($request->input($parameter->id.'_active', []));
            $parameter->activeInits()->attach($request->input($parameter->id.'_inactive', []));
        }

        return redirect('intrinsics/list/'.$id);
        
    }
    
    public function updateInitParamCategories($id, Request $request){
        $intrinsic = Intrinsic::find($id);
        
        // Get Inits of Category
        foreach($intrinsic->parameters as $parameter){
            $enabled_cats = $request->input($parameter->id."_categories");
            
            foreach($enabled_cats as $cat){
                $paramTypeInitCategory = ParameterTypeInitCategory::find($cat);
                $inits = $paramTypeInitCategory->parameterTypeInitsOfType($parameter->type->id)->get();
                foreach($inits as $init) $parameter->activeInits()->attach($init->id);
                
            }
        }

        return redirect('intrinsics/list/'.$id);
        
    }    
    
    public function showParameterTypeInitCategories()
    {
        $cats = ParameterTypeInitCategory::all();
        return view('parameterCategories', ['categories' => $cats, 'include' => 'parameterCategories.blade.php']);        
    }
    
    public function saveParameterTypeInitCategoriesDefault($id)
    {
        $cats = ParameterTypeInitCategory::all();
        foreach($cats as $cat){
            $cat->is_default = false;
            $cat->save();
        }
    
        $cat = ParameterTypeInitCategory::find($id);
        $cat->is_default = true;
        $cat->save();
        
        return redirect('parameterTypeInits/categories/list');
    }
    
    public function saveParameterTypeInitCategoriesDelete($id)
    {
        $cat = ParameterTypeInitCategory::find($id);
        $cat->delete();
    }    
    
    public function showErrors($job_id = -1, $node_id = -1)
    {
        if($job_id == -1 || $node_id == -1) $errors = Error::all();
        else $errors = Error::where('job_id', $job_id)->where('node_id', $node_id)->get();
        
        return view('errors', ['errors' => $errors, 'include' => 'errors.blade.php']);        
    }
    
    public function showVariance($job_id)
    {        
        $math = new Math;
        $job = Job::where('id', $job_id)->first();
        $instructions = Intrinsic::whereIn('id', $job["instruction_ids"])->paginate(15);
        
       
        return view('variance', ['variances' => $math->calcVariance($job_id, $instructions),
                                'job' => $job,
                                'instructions' => $instructions,
                                //'nodes' => Node::whereIn('identifier', $job["nodes"])->get(),
                                'nodes' => $job->nodes(),
                                'include' => 'variance.blade.php']);        
    }    
    
    public function test()
    {
        /*
        $templates = Template::all();
        foreach($templates as $template){
            $text = $template->template;
            $text = str_replace("\r",'', $text);
            //echo $text;
            $template->template = $text;
            $template->save();
            
        }*/

        //return Intrinsic::find(413)->parameters[0]->activeInits[0]->category;
        //return ParameterTypeInitCategory::find(1)->parameterTypeInits;
        //return ParameterTypeInit::find(58)->category;
        //return Intrinsic::find(413)->parameters[0]->activeInits()->toSql();
        //dd(Intrinsic::find(472)->parameters[0]->inactiveInits());

        //return Intrinsic::find(138)->parameters[0]->has('activeInitCategories', '<', 1)->get();
        //return Intrinsic::find(138)->parameters[0]->has('activeInitCategories')->get();
        //return ParameterTypeInitCategory::find(2)->parameterTypeInitsOfType(202)->toSql();
        
        //return ParameterTypeInitCategory::where('is_default', 1)->first()->parameterTypeInitsOfType(202)->toSql();
        
        /*
        $ptis = ParameterTypeInit::all();
        foreach($ptis as $pti){
            $pti->category()->attach('2');
        }*/
        /*
        $c = new Compilation;
        $c->name = "test";
        $c->save();

        $ce = new CompilationEntry;
        $c->entries()->save($ce);
        
        return $c;*/
        
        //$c = Compilation::find(3);
        //return $c->entries->first()->instruction;
        
        //return Job::find('5880e62789cfc');
        //return Job::find('5880e62797668b1ea17272b7');
        
        //return Job::find('587e3374aea0c')->first()->instructions()->first();
        //return Job::find('587e3b1cd69be')->nodes->last()->name;
        //return Job::find('587e3b1cd69be')->nodes();
        //return Intrinsic::find('413')->jobs;
        //return Job::find('587e3b1cd69be')->instructions();
        //return Job::find('5890a9f195368')->instructions();
        //return Intrinsic::find(413)->jobs;
        
        //$instruction = Intrinsic::find(472);
        
        
        //return $instruction->returnParameter;
        
       return Category::find(2)->instructions;
        
    }
    
    public function intrinsic2asm(){
        $wanted_params = array( 
                                40,     // __m64
                                //202,    // __m128
                                //885,    // __m128i
                                //897,    // __m128d
                                //1946,   // __m256d
                                //1949,   // __m256
                                //1999,   // __m256i
                                );
        

/*     
        echo "Checking MMX: ";
        $this->checkIntrinsic2Asm(1, $wanted_params);
        
        echo "Checking SSE: ";
        $this->checkIntrinsic2Asm(2, $wanted_params);
        
        echo "Checking SSE2: ";
        $this->checkIntrinsic2Asm(3, $wanted_params);
        
        echo "Checking SSE3: ";
        $this->checkIntrinsic2Asm(4, $wanted_params);
        
        echo "Checking SSSE3: ";
        $this->checkIntrinsic2Asm(5, $wanted_params);
        
        echo "Checking SSE4.1: ";
        $this->checkIntrinsic2Asm(6, $wanted_params);
        
        echo "Checking SSE4.2: ";
        $this->checkIntrinsic2Asm(7, $wanted_params);
        
        echo "Checking AVX: ";
        $this->checkIntrinsic2Asm(8, $wanted_params);  */ 
        
        
        $intrinsic_ids_to_convert = $this->checkIntrinsic2Asm(1, $wanted_params);
        echo "Converting:\n";
        print_r($intrinsic_ids_to_convert);
         foreach($intrinsic_ids_to_convert as $intrinsic_id){
             $this->convertIntrinsic2Asm($intrinsic_id, 46);
         }
        
    }
    
    private function checkIntrinsic2Asm($cat, $wanted_params){
        $ok_intrinsics = array();
        $intrinsics = Intrinsic::where('category_id', $cat)->get();
        $counter = 0;
        foreach($intrinsics as $intrinsic) {
            if(count($intrinsic->parameters) == 2){
                // Check Parameters
                foreach($wanted_params as $wanted_param){
                    //echo "Checking Parameter: ".$wanted_param."\n";
                    
                    $both_paras_correct = true;
                    foreach($intrinsic->parameters as $parameter )
                    {
                        if($parameter->parameter_type_id != $wanted_param){
                            $both_paras_correct = false;
                            //echo "False for param_id: ".$wanted_param."\n";
                            //echo $intrinsic;
                        }
                    }
                    if($both_paras_correct){
                        // echo $intrinsic;
                        // echo "Machted Intrinsic: ".$intrinsic->id."\n";
                        // $counter++;
                        $ok_intrinsics[] = $intrinsic->id;
                    }
                }
               // echo "\n";
            }
        }
        //echo $counter."\n";
        return $ok_intrinsics;
    }
    
    private function convertIntrinsic2Asm($intrinsic_id, $category_id){
        $intrinsic = Intrinsic::find($intrinsic_id);
        echo "Converting ".$intrinsic->intrinsic."\n";
        
        // Create new Intrinsic
        $asm = new Intrinsic();
        $asm->category_id = $category_id;
        $asm->template_id = 18; // asm
        $asm->type_id = 2;  // asm
        $asm->rettype = 202; // void
        $asm->intrinsic = $intrinsic->asm;
        $asm->active = 1;
        $asm->include = $intrinsic->include;
        
        $asm->register_type = "xmm";
        
        $asm->save();
        
        echo "New ID: ".$asm->id."\n";
        foreach($intrinsic->parameters as $parameter){
            $new_parameter = new IntrinsicParameter();
            $new_parameter->intrinsic_id = $asm->id;
            $new_parameter->parameter_type_id = $parameter->parameter_type_id;
            $new_parameter->name = $parameter->name;
            $new_parameter->immediate = $parameter->immediate;
            
            $new_parameter->save();
        }
        echo "Done\n";
    }

}

?>

