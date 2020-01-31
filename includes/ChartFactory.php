<?php

class ChartFactory {
    const ALLOWED_GRAPH_TYPES = [
        "kills",
        "deaths",
        "joins",
        "leaves"
    ];
    const CANVAS_JS_PATH = '/lib/canvasjs/canvasjs.min.js';

    /**
     * @var HtmlRenderer
     */
    private $html_renderer;

    public function __construct() {
        $this->html_renderer = new HtmlRenderer();
    }

    /**
     * @param $graph_type
     * @param $data
     * @return Tag
     * @throws Exception
     */
    public function drawChart($graph_type, $data) {
        if(!in_array($graph_type, self::ALLOWED_GRAPH_TYPES)) {
            throw new InvalidArgumentException("Graph type is not allowed.");
        }

        $graph_id = md5(rand()); // Pseudo-random, good enough for this purpose.
        $javascript = $this->prepareJavascript($graph_type, $data, $graph_id);

        return $this->html_renderer->renderTag(
            'div',
            ['class' => 'graph-container'],
            $this->html_renderer->renderInlineScript(
                $javascript
            ),
            $this->html_renderer->renderEmptyTag(
                'div',
                ['id' => $graph_id, 'class' => 'data-graph']
            )
        );
    }

    private function prepareJavascript($graph_type, $data, $graph_id) {
        $prepared_data = ["['Date', '" . ucfirst($graph_type) . "']"];

        foreach($data as $item) {
            if(!$item['time'] || !$item['value']) continue;

            $date = gmdate("Y-m-d H:i:s", $item['time']);
            $value = $item['value'];

            $prepared_data[] = "['$date', $value]";
        }

        $prepared_data = implode(',', $prepared_data);

        return "google.charts.load('current', {'packages':['corechart']});
                google.charts.setOnLoadCallback(drawChart);
        
                function drawChart() {
                    var data = google.visualization.arrayToDataTable([
                        $prepared_data
                    ]);
        
                    var options = {
                        curveType: 'function',
                        legend: { position: 'none' },
                        backgroundColor: 'transparent',
                        chartArea: {
                            backgroundColor: 'transparent',
                        },
                        hAxis: {
                            gridlines: {
                                color: '#878787'
                            },
                            textPosition: 'none'
                        },
                        vAxis: {
                            textStyle: {
                                color: 'white'
                            }
                        },
                        color: ['white'],
                        lineWidth: 2,
                        pointSize: 0,
                        width: '100%',
                        chartArea: {
                            left: '8%',
                            width: '100%'
                        },
                        crosshair: {
                            trigger: 'both',
                            orientation: 'vertical',
                            color: 'white'
                        },
                        colors: ['#009bff']
                    };
        
                    var chart = new google.visualization.LineChart(document.getElementById('$graph_id'));
        
                    chart.draw(data, options);
                    
                    // Used for tabs
                    if('$graph_type' !== '" . self::ALLOWED_GRAPH_TYPES[0] . "') document.getElementById('$graph_type-chart').style.display = 'none';
                }";
    }
}