<?php
define('IN_CB', true);
include('include/header.php');

registerImageKey('code', 'BCGupca');

$characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
?>

<div id="validCharacters">
    <h3>Valid Characters</h3>
    <?php foreach ($characters as $character) { echo getButton($character); } ?>
</div>

<div id="explanation">
    <h3>Explanation</h3>
    <ul>
        <li>Encoded as EAN-13.</li>
        <li>Most common and well-known in the USA.</li>
        <li>There is 1 number system (NS), 5 manufacturer code, 5 product code and 1 check digit.</li>
        <li>
            NS Description :
            <br />0 = Regular UPC Code
            <br />2 = Weight Items
            <br />3 = Drug/Health Items
            <br />4 = In-Store Use on Non-Food Items
            <br />5 = Coupons
            <br />7 = Regular UPC Code
            <br />And other are Reserved.
        </li>
    </ul>
</div>

<?php
include('include/footer.php');
?>