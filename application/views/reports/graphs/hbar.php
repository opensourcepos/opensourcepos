<?php
$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
$this->output->set_header("Pragma: public");

$title = new title($title);

$hbar = new hbar( '#86BBEF' );
$hbar->set_tooltip($this->lang->line('reports_revenue').': #val#' );
$y_labels = array();
$max_value = 0;
foreach($data as $label=>$value)
{
	if ($max_value < $value)
	{
		$max_value = $value;
	}
	$y_labels[] = (string)$label;
	$hbar->append_value( new hbar_value(0,(float)$value) );
}
$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->add_element( $hbar );

$step_count = $max_value > 0 ? $max_value/10 : 1;
$x = new x_axis();
$x->set_offset( false );
$x->set_steps($max_value/10);

$chart->set_x_axis( $x );

$y = new y_axis();
$y->set_offset( true );
$y->set_labels(array_reverse($y_labels));
$chart->add_y_axis( $y );

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

$chart->set_bg_colour("#f3f3f3");

$tooltip = new tooltip();
$tooltip->set_hover();
$tooltip->set_stroke( 1 );
$tooltip->set_colour( "#000000" );
$tooltip->set_background_colour( "#ffffff" ); 
$chart->set_tooltip( $tooltip );


echo $chart->toPrettyString();
?>