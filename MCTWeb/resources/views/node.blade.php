@extends('templates.master')

@section('content')
<h1>Node Info</h1>
@foreach($nodes as $node)
<dl class="dl-horizontal">
  <dt>Name</dt>
  <dd>{{$node->name}}</dd>  
  
  <dt>Identifier</dt>
  <dd>{{$node->identifier}}</dd>
  
  <dt>CPU Info</dt>
  <dd><pre class="pre-scrollable">{{$node->cpuinfo}}</pre></dd>
</dl>
@endforeach

@endsection
