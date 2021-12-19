<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;

class Node extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'identifier'; // or null
    use HybridRelations;
    
    public function isOnline(){
        $client = new Client();

        $res = $client->request('GET', 'http://localhost:15672/api/consumers', 
                                [
                                    'auth' => ['manager', 'uiThoof5']
                                ]);

        if($res->getStatusCode() == "200"){
            $nodes = json_decode($res->getBody());
            foreach($nodes as $node){
                if(strstr($node->queue->name, $this->identifier)){ 
                    return true;
                }
            }
            return false;
        }
        
        return false;
    }
}
