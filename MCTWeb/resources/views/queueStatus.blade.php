@extends('templates.master')

@section('content')
    <h1 class="page-header">Queue Status</h1>

     <table class="table table-hover">
     
        <thead>
        <tr>
            <th>Queue</th>
            <th>Name</th>
            <th>Messages</th>
            <th>State</th>
            <th>ETA</th>
            <th>Purge</th>
        </tr>
        </thead>

        <tbody>
        @foreach($queues as $queue)
        <tr>
            <th scope="row"><a>{{$queue->name}}</a></th>
            <td>
            </td>
            <td>{{$queue->messages}}</td>
            <td><span class="label {{$queue->state == "running" ? 'label-success' :'label-default'}}">{{$queue->state}}</span></td>
            <td>{{$queue->eta}}{{$queue->eta_suffix}}</td>
            <td><a href="/ampq/purgeQueue/{{$queue->name}}">X</a></td>            
        </tr>
        @endforeach
        </tbody>
        
    </table>
@endsection
