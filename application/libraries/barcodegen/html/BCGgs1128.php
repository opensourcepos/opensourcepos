<?php
define('IN_CB', true);
include('include/header.php');

$default_value['start'] = 'C';
$start = isset($_POST['start']) ? $_POST['start'] : $default_value['start'];
registerImageKey('start', $start);

$identifiers = array(
    ''    =>    'Select an identifier',
    '00'    =>    'Serial Shipping Container Code (SSCC-18)',
    '01'    =>    'Shipping Container Code (SSC)',
    '02'    =>    'Number of containers',
    '10'    =>    'Batch Number',
    '11'    =>    'Production Date',
    '12'    =>    'Due date',
    '13'    =>    'Packaging Date',
    '15'    =>    'Sell by Date (Quality Control)',
    '17'    =>    'Expiration Date',
    '20'    =>    'Product Variant',
    '21'    =>    'Serial Number',
    '240'    =>    'Additional Product Identification',
    '241'    =>    'Customer part number',
    '250'    =>    'Second Serial Number',
    '251'    =>    'Reference to source entity',
    '253'    =>    'Global Document Type Identifier',
    '30'    =>    'Quantity Each',
    '310y'    =>    'Product Net Weight in kg',
    '311y'    =>    'Product Length/1st Dimension, in meters',
    '312y'    =>    'Product Width/Diameter/2nd Dimension, in meters',
    '313y'    =>    'Product Depth/Thickness/3rd Dimension, in meters',
    '314y'    =>    'Product Area, in square meters',
    '315y'    =>    'Product Volume, in liters',
    '316y'    =>    'product Volume, in cubic meters',
    '320y'    =>    'Product Net Weight, in pounds',
    '321y'    =>    'Product Length/1st Dimension, in inches',
    '322y'    =>    'Product Length/1st Dimension, in feet',
    '323y'    =>    'Product Length/1st Dimension, in yards',
    '324y'    =>    'Product Width/Diameter/2nd Dimension, in inches',
    '325y'    =>    'Product Width/Diameter/2nd Dimension, in feet',
    '326y'    =>    'Product Width/Diameter/2nd Dimension, in yards',
    '327y'    =>    'Product Depth/Thickness/3rd Dimension, in inches',
    '328y'    =>    'Product Depth/Thickness/3rd Dimension, in feet',
    '329y'    =>    'Product Depth/Thickness/3rd Dimension, in yards',
    '330y'    =>    'Container Gross Weight (Kg)',
    '331y'    =>    'Container Length/1st Dimension (Meters)',
    '332y'    =>    'Container Width/Diameter/2nd Dimension (Meters)',
    '333y'    =>    'Container Depth/Thickness/3rd Dimension (Meters)',
    '334y'    =>    'Container Area (Square Meters)',
    '335y'    =>    'Container Gross Volume (Liters)',
    '336y'    =>    'Container Gross Volume (Cubic Meters)',
    '337y'    =>    'Kilograms per square meter',
    '340y'    =>    'Container Gross Weight (Pounds)',
    '341y'    =>    'Container Length/1st Dimension, in inches',
    '342y'    =>    'Container Length/1st Dimension, in feet',
    '343y'    =>    'Container Length/1st Dimension in, in yards',
    '344y'    =>    'Container Width/Diameter/2nd Dimension, in inches',
    '345y'    =>    'Container Width/Diameter/2nd Dimension, in feet',
    '346y'    =>    'Container Width/Diameter/2nd Dimension, in yards',
    '347y'    =>    'Container Depth/Thickness/Height/3rd Dimension, in inches',
    '348y'    =>    'Container Depth/Thickness/Height/3rd Dimension, in feet',
    '349y'    =>    'Container Depth/Thickness/Height/3rd Dimension, in yards',
    '350y'    =>    'Product Area (Square Inches)',
    '351y'    =>    'Product Area (Square Feet)',
    '352y'    =>    'Product Area (Square Yards)',
    '353y'    =>    'Container Area (Square Inches)',
    '354y'    =>    'Container Area (Square Feet)',
    '355y'    =>    'Container Area (Square Yards)',
    '356y'    =>    'Net Weight (Troy Ounces)',
    '357y'    =>    'Kilograms per square meter',
    '360y'    =>    'Product Volume (Quarts)',
    '361y'    =>    'Product Volume (Gallons)',
    '362y'    =>    'Container Gross Volume (Quarts)',
    '363y'    =>    'Container Gross Volume (Gallons)',
    '364y'    =>    'Product Volume (Cubic Inches)',
    '365y'    =>    'Product Volume (Cubic Feet)',
    '366y'    =>    'Product Volume (Cubic Yards)',
    '367y'    =>    'Container Gross Volume (Cubic Inches)',
    '368y'    =>    'Container Gross Volume (Cubic Feet)',
    '369y'    =>    'Container Gross Volume (Cubic Yards)',
    '37'    =>    'Number of Units Contained',
    '390y'    =>    'Amount payable-single monetary area',
    '391y'    =>    'Amount payable with ISO currency code',
    '392y'    =>    'Amount payable for a Variable Measure Trade Item single monetary unit',
    '393y'    =>    'Amount payable for a Variable Measure Trade Item - with ISO currency code',
    '400'    =>    'Customer Purchase Order Number',
    '401'    =>    'Consignment number',
    '402'    =>    'Shipment Identification Number',
    '403'    =>    'Routing code',
    '410'    =>    'Ship To/Deliver To Location Code (EAN13 or DUNS code)',
    '411'    =>    'Bill To/Invoice Location Code (EAN13 or DUNS code)',
    '412'    =>    'Purchase From Location Code (EAN13 or DUNS code)',
    '413'    =>    'Ship for - deliver for - forward to EAN.UCC Global Location Number',
    '414'    =>    'Identification of a physical location EAN.UCC Global Location Number',
    '415'    =>    'EAN.UCC Global Location Number of the invoicing party',
    '420'    =>    'Ship To/Deliver To Postal Code (Single Postal Authority)',
    '421'    =>    'Ship To/Deliver To Postal Code (Multiple Postal Authority)',
    '422'    =>    'Country of origin of a trade item',
    '8001'    =>    'Roll Products - Width/Length/Core Diameter',
    '8002'    =>    'Electronic Serial Number (ESN) for Cellular Phone',
    '8003'    =>    'UPC/EAN Number and Serial Number of Returnable Asset',
    '8004'    =>    'UPC/EAN Serial Identification',
    '8005'    =>    'Price per Unit of Measure',
    '8006'    =>    'Identification of the component of a trade item',
    '8007'    =>    'International Bank Account Number',
    '8018'    =>    'EAN.UCC Global Service Relation Number',
    '8020'    =>    'Payment Slip Reference Number',
    '8100'    =>    'Coupon Extended Code: Number System and Offer',
    '8101'    =>    '8101 Coupon Extended Code: Number System, Offer, End of Offer',
    '8102'    =>    'Coupon Extended Code: Number System preceded by 0',
    '90'    =>    'Mutually Agreed Between Trading Partners',
    '91'    =>    'Internal Company Codes',
    '92'    =>    'Internal Company Codes',
    '93'    =>    'Internal Company Codes',
    '94'    =>    'Internal Company Codes',
    '95'    =>    'Internal Company Codes',
    '96'    =>    'Internal Company Codes',
    '97'    =>    'Internal Company Codes',
    '98'    =>    'Internal Company Codes',
    '99'    =>    'Internal Company Codes'
);

foreach ($identifiers as $key => $value) {
    if ($key) {
        $identifiers[$key] = $key . ' - ' . $value;
    }
}

registerImageKey('code', 'BCGgs1128');

$vals = array();
for($i = 0; $i <= 127; $i++) {
    $vals[] = '%' . sprintf('%02X', $i);
}
$characters = array(
    'NUL', 'SOH', 'STX', 'ETX', 'EOT', 'ENQ', 'ACK', 'BEL', 'BS', 'TAB', 'LF', 'VT', 'FF', 'CR', 'SO', 'SI', 'DLE', 'DC1', 'DC2', 'DC3', 'DC4', 'NAK', 'SYN', 'ETB', 'CAN', 'EM', 'SUB', 'ESC', 'FS', 'GS', 'RS', 'US',
    '&nbsp;', '!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '-', '.', '/', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ':', ';', '<', '=', '>', '?',
    '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '[', '\\', ']', '^', '_',
    '`', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '{', '|', '}', '~', 'DEL'
);
?>

<ul id="specificOptions">
    <li class="option">
        <div class="title">
            <label for="start">Starts with</label>
        </div>
        <div class="value">
            <?php echo getSelectHtml('start', $start, array('NULL' => 'Auto', 'A' => 'Code 128-A', 'B' => 'Code 128-B', 'C' => 'Code 128-C')); ?>
        </div>
    </li>
    <li class="option">
        <div class="title">
            <label for="identifier">Identifiers</label>
        </div>
        <div class="value">
            <?php echo getSelectHtml('identifier', null, $identifiers); ?>
            <div id="identifierContainer"></div>
        </div>
    </li>
</ul>

<div id="validCharacters">
    <h3>Valid Characters</h3>
    <?php $c = count($characters); for ($i = 0; $i < $c; $i++) { echo getButton($characters[$i], $vals[$i]); } ?>
</div>

<div id="explanation">
    <h3>Explanation</h3>
    <ul>
        <li>Encoded as Code 128.</li>
        <li>The former correct name was UCC/EAN-128.</li>
        <li>Used for shipping containers.</li>
        <li>Based on the GS1 standard.</li>
    </ul>
</div>

<script>
(function($) {
    "use strict";

    var identifierSelect = $("#identifier"),
        identifierContainer = $("#identifierContainer"),
        generateText = $("#text");

    var updateText = function() {
        var text = "";
        $(".gs1128_identifier").each(function() {
            var $this = $(this);
            text += "(" + $this.find(".gs1128_id").val() + ")" + $this.find(".gs1128_value").val() + "~F1";
        });
        text = text.substring(0, text.length - 3);
        generateText.val(text);
    };

    var addIdentifier = function(id) {
        var identifier = $("<div class='gs1128_identifier'><input type='text' value='" + id + "' class='gs1128_id' readonly='readonly' /> - <input type='text' class='gs1128_value' /><a href='#' class='gs1128_delete'><img src='delete.png' alt='Delete' /></a></div>")
            .appendTo(identifierContainer)

        identifier.find(".gs1128_delete").on("click", function() {
            $(this).closest(".gs1128_identifier").remove();
            updateText();
            return false;
        });
        identifier.find(".gs1128_value").on("keyup", function() {
            updateText();
        });

        identifierSelect.val();
        return;
    };

    identifierSelect.change(function() {
        addIdentifier($(this).find("option:selected").val());
        updateText();
    });

    generateText.on("keyup", function() {
        var val = $(this).val(),
            section = val.split("~F1"),
            i = 0, regex = /^\(([0-9]*y?)\)(.*)$/,
            result;

        // Let's remove all identifiers we put already
        $(".gs1128_identifier").remove();
        for (i = 0; i < section.length; i++) {
            // we are able to handle only if you have ()
            result = regex.exec(section[i]);
            if (result.length === 3) {
                addIdentifier(result[1]);
                $(".gs1128_identifier").eq(i).find(".gs1128_value").val(result[2]);
            } else {
                // Oups, you entered something wrong...
                $(".gs1128_identifier").remove();
                break;
            }
        }
    });

    $(function() {
        if (generateText.val() !== "") {
            generateText.keyup();
        }
    });
})(jQuery);
</script>

<?php
include('include/footer.php');
?>