@extends('templates.master')

@section('content')
    <table class="table table-hover">
        <thead>
        <tr>
            <th>#</th>
            <th>Hostname</th>
        </tr>
        </thead>

        <tbody>
        @foreach($machines as $machine)
            <tr>
                <th scope="row"><a href="/machine/{{$machine->id}}">{{$machine->id}}</a></th>
                <td>{{$machine->hostname}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection
