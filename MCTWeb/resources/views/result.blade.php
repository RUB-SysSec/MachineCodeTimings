@extends('templates.master')

@section('content')

    <h3>Result {{$result->_id}} [<a href="/highcharts/result/{{$result->_id}}">JSON</a>]</h3>
    <script type="text/javascript">

        $(function () {
            var chart;
            $(document).ready(function () {
                $.getJSON('/highcharts/result/{{$result->_id}}', function (json) {

                    chart = new Highcharts.Chart({
                        chart: {
                            renderTo: 'container',
                            type: 'spline',
                            zoomType: 'x'
                        },

                        title: {
                            text: ''
                        },

                        yAxis: json['yAxis'],

                        series: json["series"],
                        credits: false
                    });
                });

            });

        });
    </script>

    <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
    <h4>Disassembly</h4>    
    <pre>{{$result->asm}}</pre>
    <h4>JSON Raw</h4>    
    <pre>{{json_encode($result,JSON_PRETTY_PRINT)}}</pre>

@endsection
