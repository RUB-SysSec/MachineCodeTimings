@extends('templates.master')

@section('content')
<h1>Templates</h1>

@foreach($templates as $template)
<dl class="dl-horizontal">

  <dt>Name</dt>
  <dd>
  
    <div class="form-group">
        <input class="form-control" type="text" id="{{$template->id}}_name" value="{{$template->name}}"></input>
    </div>

  </dd>
  
  <dt>ID</dt>
  <dd>{{$template->id}}</dd>
  
  <dt>Template</dt>
  {{--<dd><pre class="pre-scrollable">{{$template->template}}</pre></dd>--}}
  <dd><div id="{{$template->id}}_editor" style="position: relative; width: 100%; height: 400px; margin-bottom: 5px;">{{$template->template}}</div></dd>
  
  <dt>Options</dt>
  <dd>
    <button type="button" class="btn btn-default" onclick="updateTemplate({{$template->id}})">Save</button>
    <button type="button" class="btn btn-danger"  onclick="deleteTemplate({{$template->id}})">Delete</button>
    
    
  </dd>  
  
</dl>


<hr>
@endforeach

<script src="/js/ace/ace.js" type="text/javascript" charset="utf-8"></script>

@foreach($templates as $template)
<script>
    var editor_{{$template->id}} = ace.edit("{{$template->id}}_editor");
    editor_{{$template->id}}.setTheme("ace/theme/github");
    editor_{{$template->id}}.getSession().setMode("ace/mode/c_cpp");
</script>
@endforeach

<script>
    function updateTemplate(templateId){
        var templateName = $("#"+templateId+"_name").val();
        var editor = ace.edit(templateId+"_editor");
        var template = editor.getValue();
        
        $.post( "/api2/template/change/"+templateId, { name: templateName, template: template },
            function(data){
                if(data["success"]){
                    alert("Saved Template");
                }
                else alert("Could not save template");
            }
        
        );
    }
    
    function deleteTemplate(templateId){
        if(confirm("Are you sure?")){
            $.post( "/api2/template/delete/"+templateId, function(data){
            if(data["success"]){
                location.reload();
            }
            else alert("Could not delete template");
            });
        }
    }
</script>
@endsection
