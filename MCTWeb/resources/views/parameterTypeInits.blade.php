@extends('templates.master')

@section('content')
<h1>ParameterTypeInits</h1>
@foreach($parameterTypes as $parameterType)
	<h4>{{$parameterType->type}} <a href="/parameterTypeInits/{{$parameterType->id}}/add">[Add]</a></h4>
	@if(count($parameterType->inits))
		<table class="table table-striped">
		<thead> 
			<tr> 
				<th width="5%">id</th> 
				<th width="45%">Code</th> 
				<th width="25%">Precode</th> 
				<th width="10%">Desc</th> 
				<th width="10%">Category</th> 
				<th width="5%">Operations</th>
			</tr> 
		</thead>

		<tbody>

		@foreach($parameterType->inits as $init)
			<tr> 
				<th scope="row"><a href="/parameterTypeInits/{{$init->id}}/edit">{{$init->id}}</a></th> 
				<td>{{$init->code}}</td> 
				<td>{{$init->precode}}</td> 
				<td>{{$init->description}}</td> 
				<td>
				@foreach($init->categories as $cat)
				{{$cat->name}}
				@if(!$loop->last),@endif
				@endforeach
				</td> 
				<td><a href="/parameterTypeInits/{{$init->id}}/edit">Edit</a>, <a href="/parameterTypeInits/{{$init->id}}/delete">Delete</a></td> 
			</tr> 
		@endforeach
		 </tbody>
		</table>
	@endif
@endforeach

@endsection
