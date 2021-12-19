@extends('templates.master')

@section('content')
    <h1 class="page-header">Overview</h1>

<table id="overviewTable" class="table table-striped" >
    <thead>
        <tr>
            <th>Date (imported)</th>
            <th>Date (executed)</th>
            <th>Comment</th>
            <th>#Instructions</th>
            <th>Loop size</th>
            <th>MXCSR</th>
            <th>Flags</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    @foreach($jobs as $job)
        <tr>
            <td>{{date("Y.m.d H:i:s", $job->creation_time)}}</td>
            <td>
                @php
                if(empty($job->timestamp_user_system)){
                    echo date("Y.m.d H:i:s", $job->creation_time);
                }
                else{
                    echo date("Y.m.d H:i:s", $job->timestamp_user_system);
                }
                @endphp
            </td>
            <td>    
                    {{ $job->comment }}
            </td>
            <td>{{count($job->instruction_ids)}}</td>
            <td>{{$job->loop_size}}</td>
            <td>
            
            @if(isset($job->features["mxcsr"]))
                @foreach($job->features["mxcsr"] as $key => $value)
                    @if($value) 
                        <span class="label label-default">{{$key}}</span><br>
                    @endif
                @endforeach
            @endif
            </td>            
            <td>
                @if(isset($job->flags))               
                    @foreach($job->flags as $key => $value)
                        @if($value) 
                            <span class="label label-default">{{$key}}</span><br>
                        @endif
                    @endforeach
                @endif
            </td>
            <td>
                <a href="variance/job/{{$job->id}}">Variance</a>
                <a href="/jobs/{{$job->id}}">Edit</a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<script>
    $(document).ready(function() {
    
        $.fn.dataTable.moment( 'd.m.Y H:i:s' );        
    
        $('#overviewTable').DataTable( {
        "order": [[ 1, "desc" ]],
        "pageLength": 100
    } );
    } );
</script>
@endsection
