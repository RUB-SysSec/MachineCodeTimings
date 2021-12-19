<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


use App\Models\Result;
use App\Models\Template;
use App\Models\ParameterTypeInitCategory;
use App\Models\Job;
use App\Models\Category;
use App\Models\Node;
use App\Models\Intrinsic;


use GuzzleHttp\Client;

class AmpqController extends Controller
{
    public function prepareJob($instruction_id = "")
    {
        return view('prepareJob', [
                            'include' => 'prepareJob.blade.php',
                            'instruction_id' => $instruction_id,
                            'categories' => Category::all(),
                            'paramTypeInitCategories' => ParameterTypeInitCategory::all(),
                            'templates' => Template::all(),
                            'nodes' => Node::all()
                            ]
                   );
    }
    
    public function queueJob(Request $request)
    {

        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        $channel->queue_declare('jobs', false, true, false, false);
        
        // Create Job
        $json = array();
        $json["id"] = uniqid();
        $json["comment"] = $request->input('comment');
        $json["creation_time"] = time();

        // Instructions
        $json["instruction_ids"] = array();
        
        if($request->has('intrinsic_id')){
            $json["instruction_ids"][] = intval($request->input('intrinsic_id'));
        }
        else{
        
            $cats_selected = $request->input('categories');
            
            $intrinsics = Intrinsic::whereIn('category_id', $cats_selected )->where('active', 1)->get();
            foreach($intrinsics as $intrinsic){
                $json["instruction_ids"][] = $intrinsic->id;
            }
        }
        // Force Init categories
        if($request->input("force_init", 0)){
            $json["force_parameterTypeInitCategory"] = intval($request->input("force_paramInitCategory"));
        }
        else $json["force_parameterTypeInitCategory"] = -1;
        
        // Features
        $json["features"] = array();
        $json["features"]["mxcsr"] = array();
        
        if($request->has('mxcsr')){
            if(array_search('daz',$request->input('mxcsr')) !== FALSE) $json["features"]["mxcsr"]["daz"] = True;
            else $json["features"]["mxcsr"]["daz"] = False;
            
            if(array_search('ftz',$request->input('mxcsr')) !== FALSE) $json["features"]["mxcsr"]["ftz"] = True;
            else  $json["features"]["mxcsr"]["ftz"] = False; 
        }
        else{
            $json["features"]["mxcsr"]["daz"] = False;
            $json["features"]["mxcsr"]["ftz"] = False; 
        }
        
        
        // Nodes
        $json["node_ids"] = array();
        foreach($request->input('nodes') as $node){
            $json["node_ids"][] = $node;
        }
        
        $json["loop_size"] = intval($request->input('loop_size'));
        
        $compiler = $request->input('compiler');
        switch($compiler){
            case"gcc"       : $json["compiler"] = "gcc";        break;
            case"gcc-5.3"   : $json["compiler"] = "gcc-5.3";    break;
            case"clang"     : $json["compiler"] = "clang";      break;
        }
        $json["compiler_options"] = $request->input('compiler_options');
        $json["force_compiler_command"] = intval($request->input('force_compiler_command', 0));
        
        // Flags
        $json["flags"]["is_lownoise"] = $request->has('is_lownoise');
        $json["flags"]["is_test"] = $request->has('is_test');
        $json["flags"]["is_mctbench"] = $request->has('is_mctbench');
        $json["flags"]["is_asm"] = $request->has('is_asm');
        
        $msg = new AMQPMessage(json_encode($json), array('content_type' => 'application/json'));
        $channel->basic_publish($msg, '', 'jobs');
        
        
        return redirect('intrinsics/list/'.$json["instruction_ids"][0]);

    }
    
    public function queueJobDirectly(Request $request)
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        $channel->queue_declare('jobs', false, true, false, false);
        
        $msg = new AMQPMessage(json_encode($request->input()), array('content_type' => 'application/json'));
               
        $return = array();
        $channel->basic_publish($msg, '', 'jobs');
        $return["success"] = true;
        
        
        return $return;
    }
    
    public function queueStatus()
    {
        $client = new Client();
        $res = $client->request('GET', 'http://localhost:15672/api/queues', 
                                [
                                    'auth' => ['manager', 'uiThoof5']
                                ]);
        
        $body;
        if($res->getStatusCode() == "200"){
            $body = json_decode($res->getBody());
        
            foreach($body as $queue){
            
                if(property_exists($queue, "message_stats") && 
                   property_exists($queue->message_stats, "ack_details") && 
                   $queue->message_stats->ack_details->rate > 0){
                    $s = ($queue->messages)/($queue->message_stats->ack_details->rate);
                    $suffix = "s";
                    if($s > 60){
                        $s /= 60;
                        $suffix = "m";
                        
                        if($s>60){
                            $s /= 60;
                            $suffix = "h";
                        }
                    }
                    $s = round($s,2);
                }
                else{ 
                    $s = "";
                    $suffix = "";
                }
                
                $queue->eta = $s;
                $queue->eta_suffix = $suffix;
                
                // Check if it is really running
                if(isset($queue->idle_since)) $queue->state = "idle";
                
            }
            
            return view('queueStatus', 
            ['include' => 'queueStatus.blade.php',
                'nodes' => Node::all()->keyBy('identifier'),
                'queues' => $body
            ]);
        }
        
        return "error";
 
       
    }
    
    public function purgeQueue($name)
    {
        $client = new Client();
        $res = $client->request('DELETE', 'http://localhost:15672/api/queues/%2F/'.$name.'/contents', 
                                [
                                    'auth' => ['guest', 'guest']
                                ]);
        
        if($res->getStatusCode() == "204"){
                return redirect('/ampq/queueStatus');
        }
        
        return "error";
       
    }
    


}

?>
