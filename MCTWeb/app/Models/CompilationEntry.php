<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

class CompilationEntry extends Model
{
    protected $connection = 'mysql';
    use HybridRelations;
    
    public function compilation(){
        return $this->belongsTo('App\Models\Compilation');        
    }
    
    public function job(){
        return $this->belongsTo('App\Models\Job');        
    }

    public function node(){
        return $this->belongsTo('App\Models\Node');        
    }
    
    public function instruction(){
        return $this->belongsTo('App\Models\Intrinsic', 'intrinsic_id');        
    }
}
