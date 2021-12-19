@extends('templates.master')

@section('content')
<h1>Jobs</h1>
{{ $jobs->links() }}   
@forelse($jobs as $job)
<dl class="dl-horizontal">



  <dt>Comment</dt>
  <dd>
    <form action="/job/{{ $job->id }}/change/redirect/jobs" method="POST" class="form-inline">
    <div class="form-group">
        <input class="form-control" type="text"  name="comment" value="{{ $job->comment }}"></input>
        <button type="submit" class="btn btn-default">Save</button>
    </div>
    </form>
  </dd>

  <dt>Flags</dt>
  <dd>
    @foreach($job->flags as $key => $value)
            <a href="#" onclick="return setFlag('{{$job->id}}','{{$key}}');"><span id="{{$job->id}}_{{$key}}" class="label @if($value) label-success @else label-default @endif">{{$key}}</span></a>
    @endforeach
  </dd>  


  <dt>Date</dt>
  <dd>{{date("d.m.Y H:i:s",$job->creation_time)}}</dd>  
  
  <dt>JSON</dt>
  <dd><pre class="pre-scrollable" style="max-height: 20vh">{{json_encode($job,JSON_PRETTY_PRINT)}}</pre></dd>
  
  <dt>Options</dt>
  <dd>  
            
        <div class="btn-group">
            <button type="button" class="btn btn-success">Job</button>
            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu">
                <li><a href="/job/{{$job->id}}/delete">Delete complete Job and its Results</a></li>
            </ul>
        </div>
            
        @foreach($job->node_ids as $key => $value)
        
            @php
                $node_name = $job->nodes()->where('identifier', $value)->first()->name;
            @endphp
            
            <div class="btn-group" id="divRemoveNode_{{$value}}_{{$job->id}}">
                <button type="button" class="btn btn-success">{{$node_name}} [{{$value}}]</button>
                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="#" onclick="return removeNodeFromJob('{{$value}}', '{{$job->id}}');">Delete Node and Results from this Job</a></li>
                </ul>
            </div>
        @endforeach  
  </dd>
</dl>

<hr>
@empty
<h2>No Jobs yet</h2>
@endforelse

{{ $jobs->links() }}    

<script>

    function postProcessingFlag(data) {

        if(data['success'] == true){
            job_id = data['job_id'];
            flag = data['flag'];
            flag_value = data['flag_value'];
            if(flag_value == 1){
                $('#'+job_id+'_'+flag).removeClass('label-default').addClass('label-success');
            }
            if(flag_value == 0){
                $('#'+job_id+'_'+flag).removeClass('label-success').addClass('label-default');
            }
        }
        else alert("change failed");
        
    }
    
    function postProcessingNode(data) {

        if(data['success'] == true){
            job_id = data['job_id'];
            node_id = data['node_id'];
            $('#divRemoveNode_'+node_id+'_'+job_id).remove();
        }
        else alert("change failed");
        
    }    


    function setFlag(job_id, flag_key){
        //var catName = $("#newCatName").val();
        //alert(flag_key);
        $.ajax({
            url: '/api2/job/'+job_id+'/changeFlag',
            type: 'POST',
            data: {flag: flag_key},
            //data: catName,
            dataType: 'json',
            cache: false,
            success: postProcessingFlag,
            async:true,
            });
    };
    
    function removeNodeFromJob(node_id, job_id){
        //var catName = $("#newCatName").val();
        //alert(flag_key);
        $.ajax({
            url: '/api2/job/'+job_id+'/removeNode/'+node_id,
            type: 'GET',
            dataType: 'json',
            cache: false,
            success: postProcessingNode,
            async:true,
            });
    };    
    
</script>


@endsection
