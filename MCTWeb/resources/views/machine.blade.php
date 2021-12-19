@extends('templates.master')

@section('content')

    <h1>Host: {{$machine->hostname}}</h1>
    cpuinfo
    <pre class="pre-scrollable">{{$machine->cpuinfo}}</pre>

@endsection