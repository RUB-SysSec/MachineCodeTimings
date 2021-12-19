@extends('templates.master')

@section('content')
    <h1 class="page-header">Categories</h1>
    <table class="table table-hover">
        <thead>
        <tr>
            <th>Category</th>
        </tr>
        </thead>

        <tbody>
        @foreach($categories as $category)
        <tr>
            <th scope="row"><a href="/intrinsics/category/{{$category->id}}">{{$category->name}}</a></th>
        </tr>
        @endforeach
        </tbody>
    </table>
@endsection
