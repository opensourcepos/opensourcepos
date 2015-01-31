<?php
function baseCustomSetup($barcode, $get) {
    $font_dir = APPPATH .'libraries/barcodegen/font';
	//var_dump($font_dir);
    if (isset($get['thickness'])) {
        $barcode->setThickness(max(9, min(90, intval($get['thickness']))));
    }

    $font = 0;
    if ($get['font_family'] !== '0' && intval($get['font_size']) >= 1) {
        $font = new BCGFontFile($font_dir . '/' . $get['font_family'], intval($get['font_size']));
    }

    $barcode->setFont($font);
}
?>