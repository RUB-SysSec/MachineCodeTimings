<?php

namespace App\Models;
use App\Models\Node;
use App\Models\Intrinsic;

class Job extends EloquentMongoDB
{
    protected $primaryKey = 'id'; // or null

    public function nodes()
    {
        // db.jobs.updateMany( {}, { $rename: { "nodes": "node_ids" } } )
        //return $this->hasMany('App\Models\Node', 'identifier', 'node_ids');
        //return $this->belongsTo('App\Models\Node', 'node_ids', 'identifier');
        //return $this->belongsToMany('App\Models\Node', 'jobs', 'node_ids', 'identifier');
     
        return Node::whereIn('identifier', $this->node_ids)->get();
        //return $this->embedsMany('App\Models\Node');
    }
    
    public function instructions()
    {
        return Intrinsic::whereIn('id', $this->instruction_ids)->get();
    }

}
