<?php
class Barcode extends CI_Controller {
	function __construct() {
		parent::__construct();
	}
	
	function index($barcode) {
    if(isset($_GET["text"])) $text=$_GET["text"];
    if(isset($_GET["format"])) $format=$_GET["format"];
    if(isset($_GET["quality"])) $quality=$_GET["quality"];
    if(isset($_GET["width"])) $width=$_GET["width"];
    if(isset($_GET["height"])) $height=$_GET["height"];
    if(isset($_GET["type"])) $type=$_GET["type"];
    if(isset($_GET["barcode"])) $barcode=$_GET["barcode"];


    if(isset($_POST["text"])) $text=$_POST["text"];
    if(isset($_POST["format"])) $format=$_POST["format"];
    if(isset($_POST["quality"])) $quality=$_POST["quality"];
    if(isset($_POST["width"])) $width=$_POST["width"];
    if(isset($_POST["height"])) $height=$_POST["height"];
    if(isset($_POST["type"])) $type=$_POST["type"];
    if(isset($_POST["barcode"])) $barcode=$_POST["barcode"];


    if (!isset ($text)) $text = 1;
    if (!isset ($type)) $type = 1;
    if (empty ($quality)) $quality = 100;
    if (empty ($width)) $width = 160;
    if (empty ($height)) $height = 80;
    if (!empty ($format)) $format = strtoupper ($format);
    else $format="PNG";

    switch ($type) {
      default:
        $type = 1;
      case 1:
        $this->barcode->Barcode39($barcode, $width, $height, $quality, $format, $text);
        break;          
    }
	}	
}
?>