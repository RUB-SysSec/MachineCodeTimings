@extends('templates.master')

@section('content')
<h1>Add/Edit new init</h1>

<form class="form-horizontal" method="POST" action="/parameterTypeInits/manage">

<div class="panel panel-default">
  <div class="panel-heading">Add/Edit new init for parameter type {{$parameterType->type}}</div>
  <div class="panel-body">
    <div class="form-group">
        <label for="category" class="col-sm-2 control-label">Category</label>
        <div class="col-sm-10">
            <div id="categories_container">
                @foreach($categories as $category)
                <label class="checkbox-inline">
                    <input type="checkbox" id="categories"
                    @if(!empty($parameterTypeInit))
                        @if(array_has($parameterTypeInit->categories->keyBy('name'), $category->name))
                        checked
                        @endif
                    @endif
                    name="categories[]" value="{{$category->id}}"> {{$category->name}}
                </label>
                @endforeach
            </div>
            <br>
            <a data-toggle="modal" data-target="#myModal" href="#">Add new Category</a>
        </div>
    </div>
    <div class="form-group">
        <label for="category" class="col-sm-2 control-label">Code</label>
        <div class="col-sm-10">
        	<textarea class="form-control" type="text" name="code">{{$parameterTypeInit->code or ''}}</textarea>
        </div>
    </div>
    <div class="form-group">
        <label for="category" class="col-sm-2 control-label">PreCode</label>
        <div class="col-sm-10">
        	<textarea class="form-control" type="text" name="precode">{{$parameterTypeInit->precode or ''}}</textarea>
        </div>
    </div>

	<div class="form-group">
        <label for="category" class="col-sm-2 control-label">Description</label>
        <div class="col-sm-10">
        	<textarea class="form-control" type="text" name="description">{{$parameterTypeInit->description or ''}}</textarea>
        </div>
    </div>
    <input type="hidden" name="parameterTypeId" value="{{$parameterType->id}}"/>
    @if(!empty($parameterTypeInit))
    <input type="hidden" name="parameterTypeInitId" value="{{$parameterTypeInit->id}}"/>
    @endif

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
        <label for="newCatName"  >Name</label>
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
