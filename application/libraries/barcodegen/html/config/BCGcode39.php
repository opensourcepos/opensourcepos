<?php
$classFile = 'BCGcode39.barcode.php';
$className = 'BCGcode39';
$baseClassFile = 'BCGBarcode1D.php';
$codeVersion = '5.0.2';

function customSetup($barcode, $get) {
    if (isset($get['checksum'])) {
        $barcode->setChecksum($get['checksum'] === '1' ? true : false);
    }
}
?>