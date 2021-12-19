@extends('templates.master')

@section('content')

<h1 class="page-header">{{$job->comment}}</h1>
{{ $instructions->links() }}
<table class="table table-hover">

    <tr>

        <th>Category</th>
        <th>Name</th>
        <th>Template</th>
        @foreach($nodes as $node)
        <th>{{$node->name}}</th>
        @endforeach        
    </tr>
    
    @foreach($instructions as $instruction)
    <tr>

        <td>{{$instruction->category->name}}</td>
        <td><a href="/intrinsics/list/{{$instruction->id}}/job/{{$job->id}}" style="display: block;">{{$instruction->intrinsic}}</a></td>
        <td>{{$instruction->template->name}}</td>
        @foreach($nodes as $node)
        <?php
        
        $type = "";
        
        $val = $variances[$job->id][$instruction->id][$node->identifier]["max_variance"];
        if($val != 0){
            if($val < 100) $type = "info";
            else if($val < 1000) $type = "warning";
            else if($val > 1000) $type = "danger";
            
            $min_at_max_var = $variances[$job->id][$instruction->id][$node->identifier]["min_value_at_max_variance"];
            $min = $variances[$job->id][$instruction->id][$node->identifier]["min_value"];
        }
        else{
            $type = "";
            $val = "";
            $min = "";
            $min_at_max_var = "";
        }
        
        
        ?>
        <td class="{{$type}}"><a href="/intrinsics/list/{{$instruction->id}}/job/{{$job->id}}/node/{{$node->identifier}}" style="display: block;">{{!empty($val) ? $val.' - '.$min_at_max_var.' - '.$min : ''}}</a></td> 
        @endforeach
 
    </tr>
    @endforeach

</table>
{{ $instructions->links() }}    
@endsection
