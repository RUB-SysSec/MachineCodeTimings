<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParameterTypeInitCategory extends Model
{
	public function parameterTypeInits(){
		return $this->belongsToMany('App\Models\ParameterTypeInit');
    }

	public function parameterTypeInitsOfType($type){
		return $this->belongsToMany('App\Models\ParameterTypeInit')->where('parameter_type_id', $type);
    }  
    /*
    public function isDefault(){
        return $this->is_default;
    }*/
}


?>
