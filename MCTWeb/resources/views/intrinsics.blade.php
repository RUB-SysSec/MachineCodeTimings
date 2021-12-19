@extends('templates.master')


@section('content')
    <h1 class="page-header">Intrinsics</h1>
    <table class="table table-hover">
        <thead>
        <tr>
            <th>#</th>
            <th>CPUID</th>
            <th>Active</th>
            <th>Name</th>
            <th>ASM</th>
            <th>Description</th>
        </tr>
        </thead>

        <tbody>
        @foreach($intrinsics as $intrinsic)
        @if($intrinsic->saved)
        <tr class="danger">
        @else
        <tr>
        @endif
            <th scope="row"><a href="/intrinsics/list/{{$intrinsic->id}}">{{$intrinsic->id}}</a></th>
            <td>{{$intrinsic->cpuid_flags}}</td>
            <td>{{$intrinsic->active}}</td>
            <td>{{$intrinsic->intrinsic}}</td>
            <td>{{$intrinsic->asm}}</td>
            <td>{{$intrinsic->description}}</td>

        </tr>
        @endforeach
        </tbody>
    </table>
@endsection
