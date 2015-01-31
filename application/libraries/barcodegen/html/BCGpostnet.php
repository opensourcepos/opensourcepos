<?php
define('IN_CB', true);
include('include/header.php');

registerImageKey('code', 'BCGpostnet');

$characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
?>

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
            <br />(Those 2 digits are taken from your address. If your address is 6453, the code will be 53.)
        </li>
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