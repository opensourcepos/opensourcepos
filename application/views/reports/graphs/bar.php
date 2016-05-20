<?php
$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
$this->output->set_header("Pragma: public");
$bar = new bar_filled( '#4386a1', '#577261' );

$bar_labels = array();
$bar_values = array();

foreach($data as $label=>$value)
{
	$bar_labels[] = (string)$label;
	$bar_values[] = (float)$value;
}

$bar->set_values($bar_values);

$chart = new open_flash_chart();
$chart->set_title(new title($title));
$x = new x_axis();
$x->steps(1);
$x->set_labels_from_array($bar_labels);
$chart->set_x_axis( $x );

$y = new y_axis();
$y->set_tick_length(7);
$y->set_range(0, (count($data) > 0 ? max($data) : 0) + 25, ((count($data) > 0 ? max($data) : 0)+25)/10);
$chart->set_y_axis( $y );
$chart->set_bg_colour("#f3f3f3");

$chart->add_element($bar);

if (isset($yaxis_label))
{
	$y_legend = new y_legend($yaxis_label );
	$y_legend->set_style( '{font-size: 20px; color: #000000}' );
	$chart->set_y_legend( $y_legend );
}

if (isset($xaxis_label))
{
	$x_legend = new x_legend($xaxis_label );
	$x_legend->set_style( '{font-size: 20px; color: #000000}' );
	$chart->set_x_legend( $x_legend );
}
echo $chart->toPrettyString();
?>