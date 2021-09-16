<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * PDF helper
 */

function create_pdf($html, $filename = '')
{
    // need to enable magic quotes for the
    $dompdf = new Dompdf\Dompdf (["isRemoteEnabled" => TRUE, "isPhpEnabled" => TRUE));
    $dompdf->loadHtml(str_replace (["\n", "\r"), '', $html));
    $dompdf->render();
    
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
