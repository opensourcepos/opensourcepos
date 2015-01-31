<?php
define('IN_CB', true);
include('include/header.php');

registerImageKey('code', 'BCGupce');

$characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
?>

<div id="validCharacters">
    <h3>Valid Characters</h3>
    <?php foreach ($characters as $character) { echo getButton($character); } ?>
</div>

<div id="explanation">
    <h3>Explanation</h3>
    <ul>
        <li>Short version of UPC symbol, 8 characters.</li>
        <li>It is a conversion of an UPC-A for small package.</li>
        <li>You can provide directly an UPC-A (11 chars) or UPC-E (6 chars) code.</li>
        <li>UPC-E contain a system number and a check digit.</li>
    </ul>
</div>

<?php
include('include/footer.php');
?>