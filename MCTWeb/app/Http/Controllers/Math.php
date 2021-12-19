<?php

namespace App\Http\Controllers;

use App\Models\ParameterTypeInit;

use App\Models\Job;
use App\Models\Result;

class Math extends Api2
{
    public function calcVariance($job_id, $instructions){
        $return = array();
    
        $job = Job::find($job_id);
        //return $job;
        //$instructions_ids = $job["instruction_ids"];
        $node_ids = $job["node_ids"];
        
        foreach($instructions as $instruction){
            foreach($node_ids as $node_id){
                $min_array = array();
                foreach($this->getResultsHide($instruction->id, $job["id"], $node_id, "asm") as $result){
                     $min_array[] = $result["results"]["rdtsc"]["bench_min"];
                }
                $variance = $this->variance($min_array);
                if(count($min_array) > 0) {
                    $return[$job->id][$instruction->id][$node_id]['max_variance'] = round(max($variance), 1);
                    $return[$job->id][$instruction->id][$node_id]['min_value_at_max_variance'] = $min_array[array_search(max($variance),$variance)];
                    $return[$job->id][$instruction->id][$node_id]['min_value'] = min($min_array);
                }
                else{
                    $return[$job->id][$instruction->id][$node_id]['max_variance'] = 0;
                    $return[$job->id][$instruction->id][$node_id]['min_value_at_max_variance'] = 0;
                }
                
            }
        }
        
        //print_r($return);
        return $return;
    }
    
    private function variance($int_array){
        
        //print_r($int_array);
        $ew = 0;
        $variance = array();
        if(count($int_array) == 0){
            $variance[] = 0;
            return $variance;
        }
        
        foreach($int_array as $int){
            $ew += $int;
        }
        $ew /= count($int_array);
        
        foreach($int_array as $int){
            $variance[] = ($int-$ew)*($int-$ew);
        }
        
        /*echo "ew: ".$ew."\n";
        print_r($variance);
        echo "\n";*/
        return $variance;
    }
}

?> 
