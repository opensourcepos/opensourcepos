<?php
$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
$this->output->set_header("Pragma: public");
$line_data = array();
$labels = array();
foreach($data as $label=>$value)
{
    $line_data[] = (float)$value;
	$labels[] = (string)$label;
}

$hol = new hollow_dot();
$hol->size(3)->halo_size(1)->tooltip('#x_label#<br>#val#');

$line = new line();
$line->set_default_dot_style($hol); 
$line->set_values($line_data);

$chart = new open_flash_chart();
$chart->set_title(new title($title));
$chart->add_element($line);

$x = new x_axis();
$x->steps(count($data) > 10 ? (int)(count($data)/4) : 1);
$x->set_labels_from_array($labels);
$chart->set_x_axis( $x );

$y = new y_axis();
$y->set_tick_length(7);
$y->set_range(0, (count($data) > 0 ? max($data) : 0) + 25, ((count($data) > 0 ? max($data) : 0)+25)/10);
$chart->set_y_axis( $y );
$chart->set_bg_colour("#f3f3f3");

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