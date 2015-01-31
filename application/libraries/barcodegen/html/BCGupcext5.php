<?php
define('IN_CB', true);
include('include/header.php');

registerImageKey('code', 'BCGupcext5');

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
        <li>Used to encode suggested retail price.</li>
        <li>If the first number is a 0, the price xx.xx is expressed in British Pounds. If it is a 5, it is expressed in US dollars.</li>
        <li>
            Special Code Description:
            <br />90000: No suggested retail price
            <br />99991: The item is a complementary of another one. Normally free
            <br />99990: Used bh National Association of College Stores to mark "used book"
            <br />90001 to 98999: Internal purposes for some publishers
        </li>
    </ul>
</div>

<?php
include('include/footer.php');
?>