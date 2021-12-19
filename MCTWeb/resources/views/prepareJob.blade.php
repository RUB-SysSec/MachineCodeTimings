@extends('templates.master')

@section('content')

<h1>Queue Job</h1>
<form class="form-horizontal" method="POST" action="/ampq/queueJob">

<div class="panel panel-default">
  <div class="panel-heading">Instructions to Measure</div>
  <div class="panel-body">
    <div class="form-group">
        <label for="intrinsic" class="col-sm-2 control-label">Intrinsic</label>
        <div class="col-sm-10">
            <input type="number" class="form-control" value="{{$instruction_id}}" name="intrinsic_id" id="intrinsic" placeholder="intrinsic id">
        </div>
    </div>
    
    <div class="form-group">
        <label class="col-sm-2 control-label">Categories</label>
        
        <?php $counter = 0; ?>
        @foreach($categories as $category)
        <div class="checkbox col-sm-3">
            <label>
            <input type="checkbox" name="categories[]" value="{{$category->id}}"> {{$category->name}}
            </label>
        </div>
        <?php $counter++; ?>
        @if($counter % 3 == 0)
        <div class="checkbox col-sm-2"></div>
        @endif
        @endforeach
  
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">Init</label>
        <div class="checkbox col-sm-10">
            <label >
            <input type="checkbox" name="force_init" value="1"> Force ParameterInitCategory
            </label>
            <select class="form-control" name="force_paramInitCategory">
            @foreach($paramTypeInitCategories as $cat)
                <option value="{{$cat->id}}">{{$cat->name}}</option>
            @endforeach
            </select>
        </div>
    </div>
  </div>
</div>  
  
<div class="panel panel-default">
    <div class="panel-heading">Template Options</div>
    <div class="panel-body">      
        <div class="form-group">
            <label class="col-sm-2 control-label">Features</label>
            <div class="checkbox col-sm-10">
                <label>
                    <input type="checkbox" name="mxcsr[]" value="daz"> Denormals-Are-Zero
                </label>
                <br>
                <label>
                    <input type="checkbox" name="mxcsr[]" value="ftz"> Flush-to-Zero
                </label>        
            </div>
        </div>
        <div class="form-group">
            <label for="loop" class="col-sm-2 control-label">Loop</label>
            <div class="col-sm-10">
            <input type="number" class="form-control" name="loop_size" id="loop_size" value="100" required>
            </div>
        </div> 
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">Compiler Options</div>
    <div class="panel-body">
        <p class="help-block">These settings are only used if an instruction has no compiler command set or if <i>Force Compiler Command</i> is checked.</p>
        <div class="form-group">
            <label class="col-sm-2 control-label">Compiler</label>
            <div class="radio col-sm-10">
                <label class="">
                    <input type="radio" name="compiler" value="gcc" checked> GCC
                </label><br>
                <label class="">
                    <input type="radio" name="compiler" value="gcc-5.3">GCC 5.3
                </label><br>                
                <label class="">
                    <input type="radio" name="compiler" value="clang"> Clang
                </label>
                <p class="help-block">Make sure that the compilers are installed on the node!</p>
            </div>
        </div>
        
        <div class="form-group">
            <label for="compiler_options" class="col-sm-2 control-label">Compiler Options</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="compiler_options" id="compiler_options" value="-lm">
                <p class="help-block">Warning: The Node will always append "test.c -o test".</p>
            </div>
        </div> 
        <hr>
        <div class="form-group">
            <label class="col-sm-2 control-label"></label>
            <div class="checkbox col-sm-10">
                <label >
                <input type="checkbox" name="force_compiler_command" value="1"> Force Compiler Command
                </label>
                <p class="help-block">Override the instruction's compiler command.</p>
            </div>
        </div>
        
    </div>
</div>

  
<div class="panel panel-default">
  <div class="panel-heading">Misc</div>
  <div class="panel-body">
  
    <div class="form-group">
        <label class="col-sm-2 control-label">Nodes</label>
        <div class="checkbox col-sm-10">
            @foreach($nodes as $node)
            <label>
                <input type="checkbox" id="inlineCheckbox1" name="nodes[]" @if($node->isOnline()) checked @endif value="{{$node->identifier}}">{{$node->name}} [{{$node->identifier}}]
            </label><br>
            @endforeach
        </div>
    </div>
    
    <div class="form-group">
        <label for="comment" class="col-sm-2 control-label">Comment</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" name="comment" id="comment">
        </div>
        <label class="col-sm-2 control-label">Flags</label>
        <div class="checkbox col-sm-10">
            <label>
                <input type="checkbox" id="inlineCheckbox1" name="is_lownoise" checked>Low Noise
            </label><br>            
            <label>
                <input type="checkbox" id="inlineCheckbox1" name="is_test">Test
            </label><br>         
            <label>
                <input type="checkbox" id="inlineCheckbox1" name="is_asm">ASM
            </label><br>              
        </div>        
    </div>
    
  </div>
</div>
  
<button type="submit" class="btn btn-default">Submit</button>
</form>


@endsection
