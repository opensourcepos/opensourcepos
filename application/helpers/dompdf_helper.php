<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * PDF helper
 */

function pdf_create($html, $filename = '')
{
    // need to enable magic quotes for the
    $magic_quotes_enabled = get_magic_quotes_runtime();

    if(!$magic_quotes_enabled)
    {
    	ini_set('magic_quotes_runtime', TRUE);
    }

    $dompdf = new Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->render();

    if(!$magic_quotes_enabled)
    {
		ini_set('magic_quotes_runtime', $magic_quotes_enabled);
	}

    if($filename != '')
    {
        $dompdf->stream($filename . '.pdf');
    }
    else
    {
        return $dompdf->output();
    }
}
?>
