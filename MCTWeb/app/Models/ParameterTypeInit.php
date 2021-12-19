<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParameterTypeInit extends Model
{
    //
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function categories(){
    	return $this->belongsToMany('App\Models\ParameterTypeInitCategory', 'parameter_type_init_parameter_type_init_category');
    }

    public function type(){
    	return $this->belongsTo('App\Models\ParameterType', 'parameter_type_id');
    }

    public function intrinsicParameters(){
    	return $this->belongsToMany('App\Models\IntrinsicParameter', 'intrinsic_parameter_parameter_type_init');
    }
}


?>
