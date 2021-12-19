<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParameterType extends Model
{
    //
    public function inits(){
    	return $this->hasMany('App\Models\ParameterTypeInit');
    }
}

?>