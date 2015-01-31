<?php
define('IN_CB', true);
include('include/header.php');

$default_value['barcodeIdentifier'] = '';
$barcodeIdentifier = isset($_POST['barcodeIdentifier']) ? $_POST['barcodeIdentifier'] : $default_value['barcodeIdentifier'];
registerImageKey('barcodeIdentifier', $barcodeIdentifier);

$default_value['serviceType'] = '';
$serviceType = isset($_POST['serviceType']) ? $_POST['serviceType'] : $default_value['serviceType'];
registerImageKey('serviceType', $serviceType);

$default_value['mailerIdentifier'] = '';
$mailerIdentifier = isset($_POST['mailerIdentifier']) ? $_POST['mailerIdentifier'] : $default_value['mailerIdentifier'];
registerImageKey('mailerIdentifier', $mailerIdentifier);

$default_value['serialNumber'] = '';
$serialNumber = isset($_POST['serialNumber']) ? $_POST['serialNumber'] : $default_value['serialNumber'];
registerImageKey('serialNumber', $serialNumber);

registerImageKey('code', 'BCGintelligentmail');

$characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
?>

<ul id="specificOptions">
    <li class="option">
        <div class="title">
            <label for="barcodeIdentifier">Barcode Identifier</label>
        </div>
        <div class="value">
            <?php echo getInputTextHtml('barcodeIdentifier', $barcodeIdentifier, array('type' => 'text', 'maxlength' => 2, 'required' => 'required')); ?>
        </div>
    </li>
    <li class="option">
        <div class="title">
            <label for="serviceType">Service Type</label>
        </div>
        <div class="value">
            <?php echo getInputTextHtml('serviceType', $serviceType, array('type' => 'text', 'maxlength' => 3, 'required' => 'required')); ?>
        </div>
    </li>
    <li class="option">
        <div class="title">
            <label for="mailerIdentifier">Mailer Identifier</label>
        </div>
        <div class="value">
            <?php echo getInputTextHtml('mailerIdentifier', $mailerIdentifier, array('type' => 'text', 'maxlength' => 9, 'required' => 'required')); ?>
        </div>
    </li>
    <li class="option">
        <div class="title">
            <label for="serialNumber">Serial Number</label>
        </div>
        <div class="value">
            <?php echo getInputTextHtml('serialNumber', $serialNumber, array('type' => 'text', 'maxlength' => 9, 'required' => 'required')); ?>
        </div>
    </li>
</ul>

<div id="validCharacters">
    <h3>Valid Characters</h3>
    <?php foreach ($characters as $character) { echo getButton($character); } ?>
</div>

<div id="explanation">
    <h3>Explanation</h3>
    <ul>
        <li>Used to encode enveloppe in USA.</li>
        <li>
            You can provide
            <br />5 digits (ZIP Code)
            <br />9 digits (ZIP+4 code)
            <br />11 digits (ZIP+4 code+2 digits)
        </li>
        <li>Contains a barcode identifier, service type identifier, mailer id and serial number.</li>
    </ul>
</div>

<script>
(function($) {
    "use strict";

    $(function() {
        var thickness = $("#thickness")
            .val(9)
            .removeAttr("min step")
            .prop("disabled", true);

        $("form").on("submit", function() {
            thickness.prop("disabled", false);
        });
    });
})(jQuery);
</script>

<?php
include('include/footer.php');
?>