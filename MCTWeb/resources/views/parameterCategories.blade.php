@extends('templates.master')

@section('content')
<h1>ParameterTypeInitCategories</h1>
@foreach($categories as $cat)
<dl class="dl-horizontal">



  <dt>Name</dt>
  <dd>
    {{$cat->name}}
  </dd>
  


  <dt>Date</dt>
  <dd>{{date("d.m.Y H:i:s",$cat->creation_time)}}</dd>  
  
  <dt>JSON</dt>
  <dd><pre class="pre-scrollable">{{json_encode($cat,JSON_PRETTY_PRINT)}}</pre></dd>
  
  <dt>Options</dt>
  <dd>
  @if(!$cat->is_default)
  <a href="/parameterTypeInits/categories/makeDefault/{{$cat->id}}"><button type="button" class="btn btn-primary">Make Default</button></a>
  @else
  <button type="button" class="btn btn-default">is Default</button>
  @endif
  <a href="/parameterTypeInits/categories/delete/{{$cat->id}}"><button type="button" class="btn btn-danger">Delete</button></a>
  </dd>  
</dl>

<hr>
@endforeach

@endsection
