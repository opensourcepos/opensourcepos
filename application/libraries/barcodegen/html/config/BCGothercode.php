<?php
$classFile = 'BCGothercode.barcode.php';
$className = 'BCGothercode';
$baseClassFile = 'BCGBarcode1D.php';
$codeVersion = '5.2.0';

function customSetup($barcode, $get) {
    if (isset($get['label'])) {
        $barcode->setLabel($get['label']);
    }
}
?>