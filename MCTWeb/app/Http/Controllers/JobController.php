<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
# MySQL

# MongoDB
use App\Models\Job;
use App\Models\Error;



class JobController extends Api2
{

    // Super shitty
    public function changeJobComment($job_id, $redirect_to, Request $request){
        $this->changeJob($job_id, $request);
        
        return redirect('/'.$redirect_to);
    }

}

?>
