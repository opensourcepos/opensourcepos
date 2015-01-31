<?php
define('IN_CB', true);
include_once('include/function.php');

function showError() {
    header('Content-Type: image/png');
    readfile('error.png');
    exit;
}

$requiredKeys = array('code', 'filetype', 'dpi', 'scale', 'rotation', 'font_family', 'font_size', 'text');

// Check if everything is present in the request
foreach ($requiredKeys as $key) {
    if (!isset($_GET[$key])) {
        showError();
    }
}

if (!preg_match('/^[A-Za-z0-9]+$/', $_GET['code'])) {
    showError();
}

$code = $_GET['code'];

// Check if the code is valid
if (!file_exists('config' . DIRECTORY_SEPARATOR . $code . '.php')) {
    showError();
}

include_once('config' . DIRECTORY_SEPARATOR . $code . '.php');

$class_dir = '..' . DIRECTORY_SEPARATOR . 'class';
require_once($class_dir . DIRECTORY_SEPARATOR . 'BCGColor.php');
require_once($class_dir . DIRECTORY_SEPARATOR . 'BCGBarcode.php');
require_once($class_dir . DIRECTORY_SEPARATOR . 'BCGDrawing.php');
require_once($class_dir . DIRECTORY_SEPARATOR . 'BCGFontFile.php');

if (!include_once($class_dir . DIRECTORY_SEPARATOR . $classFile)) {
    showError();
}

include_once('config' . DIRECTORY_SEPARATOR . $baseClassFile);

$filetypes = array('PNG' => BCGDrawing::IMG_FORMAT_PNG, 'JPEG' => BCGDrawing::IMG_FORMAT_JPEG, 'GIF' => BCGDrawing::IMG_FORMAT_GIF);

$drawException = null;
try {
    $color_black = new BCGColor(0, 0, 0);
    $color_white = new BCGColor(255, 255, 255);

    $code_generated = new $className();

    if (function_exists('baseCustomSetup')) {
        baseCustomSetup($code_generated, $_GET);
    }

    if (function_exists('customSetup')) {
        customSetup($code_generated, $_GET);
    }

    $code_generated->setScale(max(1, min(4, $_GET['scale'])));
    $code_generated->setBackgroundColor($color_white);
    $code_generated->setForegroundColor($color_black);

    if ($_GET['text'] !== '') {
        $text = convertText($_GET['text']);
        $code_generated->parse($text);
    }
} catch(Exception $exception) {
    $drawException = $exception;
}

$drawing = new BCGDrawing('', $color_white);
if($drawException) {
    $drawing->drawException($drawException);
} else {
    $drawing->setBarcode($code_generated);
    $drawing->setRotationAngle($_GET['rotation']);
    $drawing->setDPI($_GET['dpi'] === 'NULL' ? null : max(72, min(300, intval($_GET['dpi']))));
    $drawing->draw();
}

switch ($_GET['filetype']) {
    case 'PNG':
        header('Content-Type: image/png');
        break;
    case 'JPEG':
        header('Content-Type: image/jpeg');
        break;
    case 'GIF':
        header('Content-Type: image/gif');
        break;
}

$drawing->finish($filetypes[$_GET['filetype']]);
?>