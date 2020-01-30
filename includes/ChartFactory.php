<?php

class ChartFactory {
    const ALLOWED_GRAPH_TYPES = [
        "Kills",
        "Deaths",
        "Joins",
        "Leaves"
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

        $graph_id = md5(rand()); // Very pseudo-random, good enough for this purpose.
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

    /**
     * @return Tag
     * @throws Exception
     */
    public function renderLoadScriptTag() {
        return $this->html_renderer->renderEmptyTag(
            'script',
            ['src' => self::CANVAS_JS_PATH]
        );
    }

    private function prepareJavascript($graph_type, $data, $graph_id) {
        $prepared_data = ["['Date', '$graph_type']"];

        foreach($data as $item) {
            if(!$item['time'] || !$item['value']) continue;

            $date = gmdate("Y-m-d h:m:s", $item['time']);
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
                            }
                        },
                        lineWidth: 1.5,
                        pointSize: 4
                    };
        
                    var chart = new google.visualization.LineChart(document.getElementById('$graph_id'));
        
                    chart.draw(data, options);
                }";
    }
}