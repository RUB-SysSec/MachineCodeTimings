<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public function instructions(){
        return $this->hasMany('App\Models\Intrinsic');
    }
}
