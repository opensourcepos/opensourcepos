<?php

/**
 * PDF helper
 */
function create_pdf(string $html, string $filename = ''): string
{
    // Security: Disable PHP execution in PDFs to prevent RCE attacks
    // Security: Disable remote file access to prevent SSRF attacks
    // Only local files referenced in HTML are allowed
    $dompdf = new Dompdf\Dompdf([
        'isRemoteEnabled' => false,
        'isPhpEnabled' => false
    ]);
    $dompdf->loadHtml(str_replace(['\n', '\r'], '', $html));
    $dompdf->render();

    if ($filename != '') {
        $dompdf->stream($filename . '.pdf');
    } else {    // TODO: Not all paths return a value.
        return $dompdf->output();
    }

    return '';
}
