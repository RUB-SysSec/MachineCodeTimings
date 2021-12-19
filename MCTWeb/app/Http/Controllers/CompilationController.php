<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
# MySQL
use App\Models\Intrinsic;
use App\Models\IntrinsicParameter;
use App\Models\Measurement;
use App\Models\Category;
use App\Models\Node;

use App\Models\Template;
use App\Models\ParameterType;
use App\Models\ParameterTypeInit;
use App\Models\ParameterTypeInitCategory;

use App\Models\Compilation;
use App\Models\CompilationEntry;

# MongoDB
use App\Models\Job;
use App\Models\Error;

use App\Http\Controllers\Math;

class CompilationController extends Controller
{
    function listCompilations($c_id = -1){
    
        return view('compilations', ['include' => 'compilations.blade.php',
                                 'compilations' => Compilation::all()
                                ]);
    }
    
    function listCompilation($c_id){
        return view('compilation', ['include' => 'compilation.blade.php',
                                    'compilation' => Compilation::find($c_id)
                                ]);
    }    
}

?>
