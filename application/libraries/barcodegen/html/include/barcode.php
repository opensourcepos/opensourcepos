<?php
// We could be more dynamic and open each file to find its name
// But that would hinder the performance
$supportedBarcodes = array(
    // 1D
    'BCGcodabar.php' => 'Codabar',
    'BCGcode11.php' => 'Code 11',
    'BCGcode39.php' => 'Code 39',
    'BCGcode39extended.php' => 'Code 39 Extended',
    'BCGcode93.php' => 'Code 93',
    'BCGcode128.php' => 'Code 128',
    'BCGean8.php' => 'EAN-8',
    'BCGean13.php' => 'EAN-13',
    'BCGgs1128.php' => 'GS1-128 (EAN-128)',
    'BCGisbn.php' => 'ISBN',
    'BCGi25.php' => 'Interleaved 2 of 5',
    'BCGs25.php' => 'Standard 2 of 5',
    'BCGmsi.php' => 'MSI Plessey',
    'BCGupca.php' => 'UPC-A',
    'BCGupce.php' => 'UPC-E',
    'BCGupcext2.php' => 'UPC Extenstion 2 Digits',
    'BCGupcext5.php' => 'UPC Extenstion 5 Digits',
    'BCGpostnet.php' => 'Postnet',
    'BCGintelligentmail.php' => 'Intelligent Mail',
    'BCGothercode.php' => 'Other Barcode',

    // Databar
    'BCGdatabarexpanded.php' => 'Databar Expanded',
    'BCGdatabarlimited.php' => 'Databar Limited',
    'BCGdatabaromni.php' => 'Databar Omni',

    // 2D
    'BCGaztec.php' => 'Aztec',
    'BCGdatamatrix.php' => 'DataMatrix',
    'BCGmaxicode.php' => 'MaxiCode',
    'BCGpdf417.php' => 'PDF417',
    'BCGqrcode.php' => 'QRCode'
);
?>