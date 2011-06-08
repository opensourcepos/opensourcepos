<?php
$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
$this->output->set_header("Pragma: public");
$title = new title($title);

$pie = new pie();
$pie->set_alpha(0.6);
$pie->set_start_angle( 35 );
$pie->add_animation( new pie_fade() );
$pie->set_tooltip( '#val# of #total#<br>#percent# of 100%' );
$pie->set_colours(get_random_colors(count($data)));

$pie_values = array();
foreach($data as $label=>$value)
{
	$pie_values[] = new pie_value((float)$value, (string)$label);
}
$pie->set_values($pie_values);
$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->set_bg_colour("#f3f3f3");
$chart->add_element( $pie );
$chart->x_axis = null;
echo $chart->toPrettyString();
?>