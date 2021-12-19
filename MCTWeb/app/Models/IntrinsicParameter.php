<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ParameterTypeInit;
use App\Models\ParameterTypeInitCategory;

class IntrinsicParameter extends Model
{
    //
    public function type(){
    	return $this->belongsTo('App\Models\ParameterType', 'parameter_type_id');
    }

    public function activeInits(){
    	return $this->belongsToMany('App\Models\ParameterTypeInit', 'intrinsic_parameter_parameter_type_init');
    }
    
    public function inits(){
        return ParameterTypeInit::where('parameter_type_id', $this->parameter_type_id)->get();
    }

    public function inits2(){ // TODO Merge with inits. Used for pagination support
        return ParameterTypeInit::where('parameter_type_id', $this->parameter_type_id);
    }    
    
    public function inactiveInits(){
         $id = $this->id;
        
        $parameter_type_inits = ParameterTypeInit::where('parameter_type_id', $this->parameter_type_id)->whereHas('intrinsicParameters', function($q) use ($id){
            $q->where('intrinsic_parameters.id', $id);
        }, '<', 1)->get();

        return $parameter_type_inits;
    }  
    
    public function inactiveInits2(){ // Same as inits2
         $id = $this->id;
        
        $parameter_type_inits = ParameterTypeInit::where('parameter_type_id', $this->parameter_type_id)->whereHas('intrinsicParameters', function($q) use ($id){
            $q->where('intrinsic_parameters.id', $id);
        }, '<', 1);

        return $parameter_type_inits;
    }  

/*
    public function inactiveInitCategories(){
        /*$id = $this->id;
        
        $parameter_type_init_categories = ParameterTypeInitCategory::where('parameter_type_id', $this->parameter_type_id)->whereHas('intrinsicParameters', function($q) use ($id){
            $q->where('intrinsic_parameters.id', $id);
        }, '<', 1)->get();

        return $parameter_type_inits;

        //return $this->
    }      */

        
}

?>
