@extends('templates.master')

@section('content')

    <h1>{{$testset->uuid}}</h1>
    <h3>Information</h3>
    <dl class="dl-horizontal">
        <dt>Description</dt>
        <dd>{{$testset->description}}</dd>
        <dt>Sourcecode</dt>
        <dd>
            <pre class="pre-scrollable">{{$testset->sourcecode}}</pre>
        </dd>
        <dt>Compiler</dt>
        <dd>
            <pre class="pre-scrollable">{{$testset->compilercommand}}</pre>
        </dd>
    </dl>


    <h3>Results</h3>
    @foreach($machines as $machine)
    <h4>{{$machine->hostname}}</h4>
    <script type="text/javascript">


        $(function () {
            var chart;
            $(document).ready(function () {
                $.getJSON('/api/time/testset/{{$id}}', function (json) {

                    chart = new Highcharts.Chart({
                        chart: {
                            renderTo: 'container',
                            type: 'spline'
                        },

                        title: {
                            text: ''
                        },

                        yAxis: [{

                            title: {
                                text: 'clock_gettime [ns]'
                            }
                        }, {
                            title: {
                                text: 'rdtsc [ticks]'
                            },
                            opposite: true
                        }],

                        series: json,
                        credits: false
                    });
                });

            });

        });
    </script>


    <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
    @endforeach
    <h3>Objdumps</h3>
    @foreach($objdumps as $objdump)
    
    <dl class="dl-horizontal">
    <dt>ObjDump</dt>
    <dd><pre class="pre-scrollable">{{$objdump->dump}}</pre></dd>
    </dl>
    
    @endforeach

@endsection
