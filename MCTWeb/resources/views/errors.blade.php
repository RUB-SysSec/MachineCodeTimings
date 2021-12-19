@extends('templates.master')

@section('content')
<h1>Errors</h1>
@foreach($errors as $error)
<dl class="dl-horizontal">



  <dt>Error</dt>
  <dd>
  <pre>
    {{json_encode($error,JSON_PRETTY_PRINT)}}
  </pre>
  </dd>  
  <dt>Stdout</dt>
  <dd><pre>
  {{base64_decode($error->stdout)}}
  </pre></dd>  
  
  <dt>Stderror</dt>
  <dd><pre>
  {{base64_decode($error->stderr)}}
  </pre></dd>  
</dl>

<hr>
@endforeach

@endsection
