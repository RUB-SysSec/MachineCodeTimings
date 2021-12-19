<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class EloquentMongoDB extends Eloquent
{
    protected $connection = 'mongodb';

}
