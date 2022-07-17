<?php
$pie_data = array();

$threshold = 0.04;
$colors = get_template_colors();

$total = 0;
foreach($data as $value)
{
	$total +=$value;	
}

$k=0;

$threshold_combined = 0;

foreach($data as $label=>$value)
{
	if ($value/$total > $threshold)
	{
		$pie_data[] = array('color' => isset($colors[$k]) ? $colors[$k] : $colors[rand(0, count($colors) -1) ] , 'value' => (float)$value, 'label' => (string)$label);
		$k++;
	}
	else
	{
		$threshold_combined+=$value;
	}
}

if ($threshold_combined)
{
	$pie_data[] = array('value' => (float)$threshold_combined, 'label' => lang('reports_other'), 'color' => '#000000');
}
?>
var canvas = document.getElementById("chart");
var ctx = canvas.getContext("2d");

var options = {
  responsive: true,
  maintainAspectRatio: false
};
<?php if (isset($tooltip_template)) { ?>
  options['tooltipTemplate'] = <?php echo json_encode($tooltip_template);?>;
<?php } ?>

<?php if (isset($legend_template)) { ?>
  options['legendTemplate'] = <?php echo json_encode($legend_template);?>;
<?php } ?>

var pieChart = new Chart(ctx).Pie(<?php echo json_encode($pie_data); ?>, options);

$('#chart-legend').append(pieChart.generateLegend());