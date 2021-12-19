@extends('templates.master')

@section('content')
    <table class="table table-hover">
        <thead>
        <tr>
            <th>#</th>
            <th>Size</th>
            <th>Comment</th>
        </tr>
        </thead>

        <tbody>
        @foreach($compilations as $compilation)
            <tr>
                <th scope="row"><a href="/compilation/list/{{$compilation->id}}">{{$compilation->id}}</a></th>
                <th scope="row">{{count($compilation->entries)}}</th>
                <td></td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection
