@extends('templates.master')

@section('content')

@foreach($compilation->entries as $entry)
@php
    $job = $entry->job;
    $node = $entry->node;
    $intrinsic = $entry->instruction;
@endphp
  
@include("highcharts/instruction_compilation")
            
@endforeach


@foreach($compilation->entries as $entry)
@php
    $job = $entry->job;
    $node = $entry->node;
    $intrinsic = $entry->instruction;
@endphp
    
    <div class="panel panel-default">
        <div class="panel-heading" role="tab">
            <h4 class="panel-title">
                Rep: {{$job->repetitions}}, Compiler: {{$job->compiler}} {{$job->compiler_options}}, {{ !empty($job->comment) ? 'Comment: '.$job->comment : ''}}<br>
                Date: {{ date("d.m.Y H:i:s", $job->creation_time)}}
            </h4>
        </div>
        @if($loop->first)
        <script>
            $( document ).ready(function() { $( "#{{$job->id}}" ).trigger( "click" ); });
        </script>
        @endif

        
        <div class="panel panel-success" style="margin:5px;">
            <div class="panel-heading">
                <h3 class="panel-title"><a href="/nodes/list/{{$node->identifier}}">{{$node->name}}</a> [<a href="/highcharts/instruction/{{$intrinsic->id}}/job/{{$job->id}}/node/{{$node->identifier}}">JSON</a>]</h3>
            </div>        
            <div class="panel-body" >
                <div id="highchart_{{$job->id}}{{$node->identifier}}" data-loaded="false"></div>
            </div>
        </div>


    </div>
@endforeach


@endsection
