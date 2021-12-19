@extends('templates.master')

@section('content')
   
    <ul class="nav nav-pills">
    
    @if (isset($intrinsic_prev))
        <li class="pull-left"><a href="/intrinsics/list/{{$intrinsic_prev->id}}"><span class="glyphicon glyphicon glyphicon-chevron-left" aria-hidden="true"></span>{{ $intrinsic_prev->intrinsic }}</a></li>
    @endif
    
    @if (isset($intrinsic_next))
        <li class="pull-right"><a href="/intrinsics/list/{{$intrinsic_next->id}}">{{ $intrinsic_next->intrinsic }}<span class="glyphicon glyphicon glyphicon-chevron-right" aria-hidden="true"></span></a></li>
    @endif
    
    </ul>
    <script>

        function postProcessing(data) {
            var myArray = data;
            if(myArray['success'] == true){

                if(myArray['state'] == 1){ 
                    $("#messages").append("<div class=\"alert alert-success\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\">x</button>Saved</div>")
                    document.getElementById("savebutton").textContent = "Unsave";
                }
                else{
                    $("#messages").append("<div class=\"alert alert-success\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\">x</button>Unsaved</div>")
                    document.getElementById("savebutton").textContent = "Save";
                }
            }
            else{
                $("#messages").append("<div class=\"alert alert-danger\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\">x</button>Error</div>")
            }
            
        }


        function saveIntrinsic(){
            $.ajax({
                url: '/api2/instruction/{{$intrinsic->id}}/save',
                type: 'get',
                dataType: 'json',
                cache: false,
                success: postProcessing,
                async:true,
                });
        };
        
    </script>
    
    <h1>{{ $intrinsic->intrinsic}}
    <button id="savebutton" type="button" class="btn btn-success" onclick="saveIntrinsic()">@if($intrinsic->saved)Unsave @else Save @endif</button>
    <a href="/ampq/prepareJob/{{$intrinsic->id}}"><button id="testbutton" type="button" class="btn btn-default">Test</button></a></h1>
    <div id="messages"></div>
    

    
    <div class="panel panel-default">
        <div class="panel-heading" role="tab">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" href="#collapse_information" aria-expanded="false" aria-controls="collapseOne" id="parameters">Information</a>
            </h4>
        </div>

        <div id="collapse_information" class="panel-collapse collapse **in**" role="tabpanel">
                <div class="panel-body">
                    <dl class="dl-horizontal">
                        <dt>Name</dt>
                        <dd>{{ $intrinsic->intrinsic}}</dd>
                        <dt>Template</dt>
                        <dd>
                            <select id="template_select">
                            @foreach($templates as $template)
                            <option @if($template == $intrinsic->template) selected @endif value="{{$template->id}}">
                            {{$template->name}}
                            </option>
                            @endforeach
                            </select>
                            
                        </dd>
                        <dt>Type</dt>
                        <dd> {{ $intrinsic->type->name}}</dd>        
                        <dt>Assembly</dt>
                        <dd>{{ $intrinsic->asm}}</dd>
                        <dt>Precode</dt>
                        <dd>{{ $intrinsic->precode}}</dd>                        
                        <dt>Category</dt>
                        <dd>{{ $intrinsic->category->name}}</dd>
                        <dt>Description</dt>
                        <dd>{{ $intrinsic->description}}</dd>
                        <dt>Operation</dt>
                        <dd>
                            <pre>{{ $intrinsic->operation}}</pre>
                        </dd>
                        <dt>Compiler Command</dt>
                        <dd>
                            <pre>{{ !empty($intrinsic->compiler_command) ? $intrinsic->compiler_command : 'None unique'}}</pre>
                        </dd>        
                    </dl>
                    
                </div>
            </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading" role="tab">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" href="#collapse_parameters" aria-expanded="false" aria-controls="collapseOne" id="parameters">Parameters</a>
            </h4>
        </div>

        <div id="collapse_parameters" class="panel-collapse collapse **in**" role="tabpanel">
    
                <div class="panel-body">

                    <form method="POST" action="/intrinsics/{{$intrinsic->id}}/updateInitParams">
                    <table class="table"> 
                        <!--<caption>Optional table caption.</caption> -->
                        <thead> 
                            <tr>
                                <th></th> 
                                @foreach($intrinsic->parameters as $parameter)
                                    <th>[Type: {{$parameter->type->id}}] [Id: {{$parameter->id}}] @if($parameter->immediate === 1) [Immediate]  @endif {{$parameter->type->type}} {{$parameter->name}}</th> 
                                @endforeach
                            </tr> 
                        </thead> 

                        <tbody>
                            <tr>
                                <th>Active</th> 
                                @foreach($intrinsic->parameters as $parameter)
                                <td id="active_{{$parameter->id}}">
                                    @foreach($parameter->activeInits as $init)
                                    <div class="checkbox">
                                      <label title="{{$init->id}}">
                                        <input type="checkbox" name="{{$parameter->id}}_active[]" value="{{$init->id}}" title="{{$init->id}}">
                                        @if(empty($init->description))
                                            {{$init->code}} 
                                        @else
                                            {{$init->description}}
                                        @endif
                                        
                                      </label>
                                    </div>
                                    @endforeach
                                    <button type="button" onclick="toggleParameters('active_{{$parameter->id}}');">Toggle</button>
                                </td>
                                @endforeach
                            </tr>
                            <tr> 
                                <th>Inactive</th> 
                                @foreach($intrinsic->parameters as $parameter)
                                <td id="inactive_{{$parameter->id}}">
                                    
                                    @foreach($parameter->inactiveInits() as $init)
                                        <div class="checkbox">
                                          <label title="{{$init->id}}">
                                            <input type="checkbox" name="{{$parameter->id}}_inactive[]" value="{{$init->id}}" title="{{$init->id}}">
                                            @if(empty($init->description))
                                            {{$init->code}} 
                                            @else
                                            {{$init->description}}
                                            @endif
                                            
                                          </label>
                                        </div>
                                    @endforeach
                                    <button type="button"  onclick="toggleParameters('inactive_{{$parameter->id}}');">Toggle</button>
                                </td> 
                                @endforeach
                            </tr>
                            <tr><td colspan="3"><button type="submit">Save</button></td></tr>
                            </form>
                            <form method="POST" action="/intrinsics/{{$intrinsic->id}}/updateInitParamCategories">
                            <tr>
                            <th>Categories</th> 
                                @foreach($intrinsic->parameters as $parameter)
                                <td>
                                    @foreach($parameterTypeInitCategories as $cat)
                                    <div class="checkbox">
                                      <label>
                                        <input type="checkbox" name="{{$parameter->id}}_categories[]" value="{{$cat->id}}">
                                        {{$cat->name}} [{{count($cat->parameterTypeInitsOfType($parameter->type->id)->get())}}]
                                      </label>
                                    </div>
                                    @endforeach
                                </td> 
                                @endforeach
                            </tr>
                            <tr><td colspan="3"><button type="submit">Save</button></td></tr>                            
                        </tbody>
                    </table>
                    
                    </form>
                    
                    
                </div>
            </div>
    </div>
    
    
    <h3>Jobs</h3>    
    @foreach($intrinsic->jobs as $job)
        @if(in_array($job->id, $job_ids) || empty($job_ids))
            @foreach($job->nodes() as $node)
                @if(in_array($node->identifier, $node_ids) || empty($node_ids))
                    @include("highcharts/instruction_default")    
                @endif
            @endforeach
        @endif
    @endforeach

    @forelse($intrinsic->jobs()->orderBy("creation_time", 'DESC')->get() as $job)
    @if(in_array($job->id, $job_ids) || empty($job_ids))
    <div class="panel panel-default">
            <div class="panel-heading" role="tab">
                <h4 class="panel-title">
                    <a role="button" data-toggle="collapse" href="#collapse_{{$job->id}}" aria-expanded="false" aria-controls="collapseOne" id="{{$job->id}}">
                    Rep: {{$job->repetitions}}, Compiler: {{$job->compiler}} {{$job->compiler_options}}, {{ !empty($job->comment) ? 'Comment: '.$job->comment : ''}}<br>
                    Date: {{ date("d.m.Y H:i:s", $job->creation_time)}}</a>
                </h4>
            </div>
            @if($loop->first || !empty($job_ids))
            <script>
                $( document ).ready(function() { $( "#{{$job->id}}" ).trigger( "click" ); });
            </script>
            @endif

            <div id="collapse_{{$job->id}}" class="panel-collapse collapse **in**" role="tabpanel">
            @foreach($job->nodes() as $node)
                @if(in_array($node->identifier, $node_ids) || empty($node_ids))

                <div class="panel panel-success" style="margin:5px;">
                    <div class="panel-heading">
                        <h3 class="panel-title"><a href="/nodes/list/{{$node->identifier}}">{{$node->name}}</a> [<a href="/highcharts/instruction/{{$intrinsic->id}}/job/{{$job->id}}/node/{{$node->identifier}}">JSON</a>] [<a href="/intrinsics/list/{{$intrinsic->id}}/job/{{$job->id}}/node/{{$node->identifier}}">Link</a>]</h3>
                    </div>        
                    <div class="panel-body" >
                        <div id="highchart_{{$job->id}}_{{$node->identifier}}" data-loaded="false"></div>
                    </div>
                </div>
                @endif
            @endforeach
            </div>
        </div>
    @endif
    @empty
    <h4>No Jobs yet</h4>
    @endforelse
    
    
<script>
    function toggleParameters(id){
        $("#"+id+" input").trigger('click'); 
    }
</script>
<script>
    $( "#template_select" ).change(function() {
    
        var template_id = $( "#template_select option:selected" ).val();
    
        $.ajax({
                url: '/api2/instruction/{{$intrinsic->id}}/template/'+template_id,
                type: 'get',
                dataType: 'json',
                cache: false,
                success: callback_template_changed,
                async: true,
                });
            
    });
    
</script>

<script>
        function callback_template_changed(data) {
            if(data['success'] == true){

                alert("Template changed");
            }
            else{
                alert("Failed to change template");
            }    
        }    
</script>

@endsection
