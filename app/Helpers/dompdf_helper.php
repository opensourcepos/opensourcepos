<?php

/**
 * PDF helper
 */
function create_pdf(string $html, string $filename = ''): string
{
    // need to enable magic quotes for the
    $dompdf = new Dompdf\Dompdf (['isRemoteEnabled' => true, 'isPhpEnabled' => true]);
    $dompdf->loadHtml(str_replace (['\n', '\r'], '', $html));
    $dompdf->render();

    if($filename != '')
    {
        $dompdf->stream($filename . '.pdf');
    }
    else//TODO: Not all paths return a value.
    {
        return $dompdf->output();
    }

    return '';
}
