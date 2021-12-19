<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Illuminate\Database\Eloquent\Model;


class Intrinsic extends Model
{
    public $timestamps = false;
    //protected $connection = 'mysql';
    use HybridRelations;
    
    public function template(){
        return $this->belongsTo('App\Models\Template');
    }

    public function type(){
        return $this->belongsTo('App\Models\Type');
    }
    
    public function category(){
        return $this->belongsTo('App\Models\Category');        
    }

    public function parameters(){
        return $this->hasMany('App\Models\IntrinsicParameter');
    }
    
    public function returnParameter(){
        return $this->belongsTo('App\Models\ParameterType', 'rettype');
    }
    
    public function jobs(){
        return $this->hasMany('App\Models\Job', 'instruction_ids');
    }
}
