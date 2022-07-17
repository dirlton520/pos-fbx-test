<?php
$labels = array_keys($data);
$dataset = array();

foreach($data as $label=>$value)
{
	$dataset[] = $value;
}

?>
var canvas = document.getElementById("chart");
var ctx = canvas.getContext("2d");
var data = {
    labels: <?php echo json_encode($labels); ?>,
    datasets: [
        {
            label: <?php echo json_encode($title); ?>,
            fillColor: "rgba(220,220,220,0.2)",
            strokeColor: "rgba(220,220,220,1)",
            pointColor: "rgba(220,220,220,1)",
            pointStrokeColor: "#fff",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(220,220,220,1)",
            data: <?php echo json_encode($dataset);?>
        }
    ]
};
new Chart(ctx).Line(data, {
  responsive: true,
  maintainAspectRatio: false,
  tooltipTemplate: <?php echo json_encode($tooltip_template);?>,
  omitXLabels: <?php echo count($labels) > 31 ? 'true' : 'false'?>
});