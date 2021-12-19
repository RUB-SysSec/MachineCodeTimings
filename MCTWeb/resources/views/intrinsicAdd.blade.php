@extends('templates.master')

@section('content')
<h1>Add/Edit new init</h1>

<form class="form-horizontal" method="POST" action="/intrinsics/add">

<div class="panel panel-default">
  <div class="panel-heading">Add new instruction</div>
  <div class="panel-body">
    <div class="form-group">
        <label for="category" class="col-sm-2 control-label">Category</label>
        <div class="col-sm-10">
            <div id="categories_container">
                <label>            
                <select name="category"  class="form-control">
                @foreach($categories as $category)
                    <option value="{{$category->id}}">{{$category->name}}</option>
                @endforeach
                </select>
                
                </label> <a href="#">Add (Todo)</a>
            </div>
           {{-- <br>
            <a data-toggle="modal" data-target="#myModal" href="#">Add new Category</a> --}}
        </div>
    </div>
    <div class="form-group">
        <label for="precode" class="col-sm-2 control-label">PreCode</label>
        <div class="col-sm-10">
        	<textarea class="form-control" type="text" id="precode" name="precode"></textarea>
        	<p class="help-block">Code executed before instrction</p>
        </div>
    </div>    
    <div class="form-group">
        <label for="category" class="col-sm-2 control-label">Instruction</label>
        <div class="col-sm-10">
        	<textarea class="form-control" type="text" name="instruction">Test</textarea>
        	<p class="help-block">Instruction name without parentheses</p>
        </div>
    </div>
    <div class="form-group">
        <label for="category" class="col-sm-2 control-label">Parameters</label>
        <div class="col-sm-10">
        	<textarea class="form-control" type="text" name="parameters" rows="4">int a1, michgibtesnicht* b2</textarea>
        	<p class="help-block">parameterType parameterName, e.g. 'int counter, char* plaintext'. Separated by commas.</p>
        </div>
        
    </div>
    
    <div class="form-group">
        <label for="category" class="col-sm-2 control-label">Return type</label>
        <div class="col-sm-10">
            <div id="categories_container">
                <label>            
                <select name="rettype"  class="form-control">
                @foreach($paraTypes as $category)
                    <option value="{{$category->id}}">{{$category->type}}</option>
                @endforeach
                </select>
                </label> <a href="#">Add (Todo)</a>
                <p class="help-block">Select void for none.</p>
            </div>
           {{-- <br>
            <a data-toggle="modal" data-target="#myModal" href="#">Add new Category</a> --}}
        </div>
    </div>
    
	<div class="form-group">
        <label for="category" class="col-sm-2 control-label">Headers</label>
        <div class="col-sm-10">
        	<input type="text" class="form-control" type="text" name="include_headers">
        	<p class="help-block">List of headers to include, e.g., 'stdio.h, &#60;time.h&#62;, "localheader.h"'. Separated by commas. Headers without an indicating prefix are included as system headers.</p>
        </div>
    </div>
    
	<div class="form-group">
        <label for="category" class="col-sm-2 control-label">Compiler command</label>
        <div class="col-sm-10">
        	<input type="text" class="form-control" type="text" name="compiler_command">
        	<p class="help-block">Warning: The node always appends 'test.c -o test'.</p>
        </div>
    </div>
    
	<div class="form-group">
        <label for="category" class="col-sm-2 control-label">Comment</label>
        <div class="col-sm-10">
        	<textarea class="form-control" type="text" name="comment"></textarea>
        	<p class="help-block"></p>
        </div>
    </div>
  </div>
</div>

<button type="submit" class="btn btn-default">Submit</button>
</form>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Add new Category</h4>
      </div>
      <div class="modal-body">
        <label for="newCatName">Name</label>
        <input type="text" id="newCatName" class="form-control"/>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="return saveIntrinsic();">Add</button>
      </div>
    </div>
  </div>
</div>


<script>

    function postProcessing(data) {
        
        if(data['success'] == true){
            $('#categories_container').append("<label class='checkbox-inline'><input type='checkbox' value='"+data['id']+"' name='categories[]'>"+data['name']+"</label>");
            $('#myModal').modal('hide');
        }
        else alert("save failed");
        
    }


    function saveIntrinsic(){
        var catName = $("#newCatName").val();
        $.ajax({
            url: '/api2/parameterTypeInitCategory/add/'+catName,
            //url: '/api2/parameterTypeInitCategory/add/',
            //type: 'POST',
            //data: {catName: 'test'},
            //data: catName,
            dataType: 'json',
            cache: false,
            success: postProcessing,
            async:true,
            });
    };
    
    </script>


@endsection
