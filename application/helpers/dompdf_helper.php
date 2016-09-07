<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
function pdf_create($html, $filename='', $stream=TRUE) 
{
	// need to enable magic quotes for the
	$magic_quotes_enabled = get_magic_quotes_runtime();
    if(!$magic_quotes_enabled)
    {
    	ini_set("magic_quotes_runtime", true);
    }
    
    $dompdf = new Dompdf\Dompdf();
    $dompdf->load_html($html);
    $dompdf->render();
    ini_set("magic_quotes_runtime", $magic_quotes_enabled);

    if ($stream) {
        $dompdf->stream($filename.".pdf");
    } else {
        return $dompdf->output();
    }
}
?>  