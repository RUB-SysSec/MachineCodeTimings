<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compilation extends Model
{
    public function entries(){
        return $this->hasMany('App\Models\CompilationEntry');
    }
}
