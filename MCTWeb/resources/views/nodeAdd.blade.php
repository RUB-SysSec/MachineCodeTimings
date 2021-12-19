@extends('templates.master')

@section('content')
<h1>AddNode</h1>

<form class="form-horizontal" method="POST" action="/nodes/add">

<div class="panel panel-default">
  <div class="panel-heading">Add a new Node</div>
  <div class="panel-body">
  
    <div class="form-group">
        <label for="category" class="col-sm-2 control-label">Name</label>
        <div class="col-sm-10">
        	<input class="form-control" type="text" name="name"/>
        	<p class="help-block">Human readable name for the Node.</p>
        </div>
    </div>  
  
    <div class="form-group">
        <label for="category" class="col-sm-2 control-label">Identifier</label>
        <div class="col-sm-10">
        	<input class="form-control" type="text" name="identifier"/>
        	<p class="help-block">Identifier of Node. This is randomly generated on its first start.</p>
        </div>
    </div>
    
    <div class="form-group">
        <label class="col-sm-2 control-label">Default</label>
        <div class="checkbox col-sm-10">
            <label>
                <input type="checkbox" name="default_enabled" value="1">
            </label>
            <p class="help-block">Selected by default in prepareJob</p>
        </div>
    </div>
    
  </div>
</div>

<button type="submit" class="btn btn-default">Add</button>
</form>

@endsection
