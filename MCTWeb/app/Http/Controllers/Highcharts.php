<?php

namespace App\Http\Controllers;

use App\Models\ParameterTypeInit;

use App\Models\Job;
use App\Models\Result;
use App\Models\Setting;

class Highcharts extends Api2
{
    public function hcResult($result_id)
    {
        $result = $this->getResult($result_id);
        
        $return = array();
        $return["series"] = array();
        $final = array();
        $keys = array();
        
        foreach($result["results"]["rdtsc"] as $key => $val){
            if(is_array($val)) $keys[] = $key;
        }
        
        foreach($keys as $key)
        {
            $tmp = array();
            $tmp["name"] = $key;
            $tmp['dataLabels']['enabled'] = false;
            $tmp['dataLabels']['format'] = '{point.y:.3f}';
            $tmp['data'] = array();
            
            for($i = 0; $i < count($result["results"]["rdtsc"][$key]); $i++){
                $tmp['data'][] = $result["results"]["rdtsc"][$key][$i];
            }
            array_push($final, $tmp);
        }
        $return["series"] = $final;
        
        
        // Axis
        $yaxis = array();
        $yaxis["title"]["text"] = "rdtsc [ticks]";
        $yaxis["showEmpty"] = false;
        $return["yAxis"] = $yaxis;
        
        
        return $return;
        

    }
    
    public function hcResults($instruction_id, $job_id, $node_id)
    {
        $job = $this->getJob($job_id);
        $results = $this->getResults($instruction_id, $job_id, $node_id);

        $return = array();
        $return["series"] = array();
        
        $setting = Setting::first();
        
        $seriesToShow = explode(',',$setting->highcharts_show_series_default);
        
        $final = array();
        $keys = array();
        // Get keys of first result
        foreach($results[0]["results"]["rdtsc"] as $key => $val){
            if(!is_array($val) &&  $key !== "max_dev") $keys[] = $key;
        } 
        
        foreach($keys as $key)
        {
            $tmp = array();
            $tmp["name"] = $key;
            
            $visible = false;
            if(in_array($key,$seriesToShow)) $visible = true;
            
            $tmp['visible'] = $visible;
            $tmp['dataLabels']['enabled'] = false;
            $tmp['dataLabels']['format'] = '{point.y:.3f}';
            $tmp['turboThreshold'] = 0;
            $tmp['data'] = array();
            
            foreach($results as $result){  

                $point = array();
                $point['y'] = $result["results"]["rdtsc"][$key];
                $point['id'] = $result["_id"];
                $tmp['data'][] = $point;
                
            }
            array_push($final, $tmp);
        }
        $return["series"] = $final;

        /*
        // Ranges
        $ranges = array();
        $ranges["name"] = "ranges";
        $ranges["type"] = "arearange";
        $ranges["linewidth"] = "0";
        $ranges["fillOpacity"] = "0.1";
        $ranges["color"] = "Highcharts.getOptions().colors[0]";
        
        $ranges["data"] = array();
        if(isset($results[0]["results"]["rdtsc"]["max"]) && isset($results[0]["results"]["rdtsc"]["min"])){
            
            foreach($results as $result){  
                $range = array();
                $range[] = $result["results"]["rdtsc"]["min"];
                $range[] = $result["results"]["rdtsc"]["max"];
                
                $ranges["data"][] = $range;
            }
        }
        //$return["series"][] = $ranges;
        array_push($return["series"], $ranges);
        */
        
        // Axis
        $yaxis = array();
        $yaxis["title"]["text"] = "rdtsc [ticks]";
        $yaxis["showEmpty"] = false;
        $return["yAxis"] = $yaxis;
        
        // Parameters
        // Tooltip
        $parameterTypeInits = ParameterTypeInit::withTrashed()->get()->keyBy('id'); // Used for plotbands and parameter tooltips
        
        
        $parameters = array();
        foreach($results as $result){  
            foreach($result["parameters"] as $paraKey => $paraValue){
                $parameter = $parameterTypeInits[$paraValue];
                $parameters[$paraKey] = $parameter->code;
            }
            $return['parameters'][] = $parameters;
        }
        
        // Plotbands
        $plotbands = array();
        $counter = 0;
        $start = 0;
        
        $first_key = key($results[0]["parameters"]);
        $paraValue = $results[0]["parameters"][$first_key];
        
        $countOfParameters = count($results[0]["parameters"]);
        
        foreach($results as $result){
            if($paraValue != $result["parameters"][$first_key] || $counter == count($results)-1){
                // First parameter changed
                
                $paraTmp["from"] = $start;
                if($counter == count($results)-1) $paraTmp["to"] = $counter;
                else $paraTmp["to"] = $counter-($countOfParameters-1); // We want a small spacing between parameters if we have two parameters
                
                $paraTmp["color"] = '#EBEBEB';

                $parameterInit = $parameterTypeInits[$paraValue];
                $label = array();
                if(empty($parameterInit->description)) $label["text"] = $parameterInit->code;
                else $label["text"] = $parameterInit->description;
                
                $label["rotation"] = "90";
                $label["textAlign"] = "left";
                $paraTmp["label"] = $label;
                
                $plotbands[] = $paraTmp;

                $start = $counter;
            }
            $counter++;
            $paraValue = $result["parameters"][$first_key];            
        }
        
        
        $return["plotbands"] = $plotbands;
        
        return $return;   
    }
    
}

?> 
