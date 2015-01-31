<?php
define('IN_CB', true);
include('include/header.php');

registerImageKey('code', 'BCGupcext2');

$characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
?>

<div id="validCharacters">
    <h3>Valid Characters</h3>
    <?php foreach ($characters as $character) { echo getButton($character); } ?>
</div>

<div id="explanation">
    <h3>Explanation</h3>
    <ul>
        <li>Extension for UPC-A, UPC-E, EAN-13 and EAN-8.</li>
        <li>Used for encode additional information for newspaper, books...</li>
    </ul>
</div>

<?php
include('include/footer.php');
?>