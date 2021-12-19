        <script type="text/javascript">
            $( document ).ready(function() {

                   
                    if(!$('#highchart_{{$job->id}}{{$node->identifier}}').data('loaded'))
                    {
                        $.getJSON('/highcharts/instruction/{{$intrinsic->id}}/job/{{$job->id}}/node/{{$node->identifier}}', function (json) {
                        
                            $('#highchart_{{$job->id}}{{$node->identifier}}').data('loaded', true);
                            chart = new Highcharts.Chart({
                                chart: {
                                    renderTo: 'highchart_{{$job->id}}{{$node->identifier}}',
                                    type: 'spline',
                                    zoomType: 'x'
                                },
                                
                                tooltip: {
                                    formatter: function() {
                                        var tooltip = '<b>'+ this.x +": "+ this.y +' ticks</b><br>';
                                        
                                        $.each(json['parameters'][this.x], function(key) {
                                            tooltip += '<br/>' + key + ": " + this;
                                        });
                                        return tooltip;
                                    },
                                    shared: false
                                },
                                legend: {
                                    enabled: true
                                },

                                title: {
                                    text: ''
                                },

                                yAxis: json['yAxis'],

                                xAxis: {
                                    type: 'category',
                                    labels: {
                                        rotation: 45,
                                        style: {
                                            fontSize: '13px',
                                            fontFamily: 'Verdana, sans-serif'
                                        }
                                    },
                                    plotBands: json['plotbands']
                                },

                                plotOptions: {
                                    series: {
                                        cursor: 'pointer',
                                        point: {
                                            events: {
                                                click: function () {
                                                    location.href = "/result/"+this.options.id;
                                                }
                                            }
                                        }
                                    }
                                },

                                series: json['series'],
                                credits: false
                            });
                        
                    });
                    }

            });
        </script>
