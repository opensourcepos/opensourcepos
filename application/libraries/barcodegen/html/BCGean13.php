<?php
define('IN_CB', true);
include('include/header.php');

registerImageKey('code', 'BCGean13');

$characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
?>

<div id="validCharacters">
    <h3>Valid Characters</h3>
    <?php foreach ($characters as $character) { echo getButton($character); } ?>
</div>

<div id="explanation">
    <h3>Explanation</h3>
    <ul>
        <li>EAN means Internal Article Numbering.</li>
        <li>It is an extension of UPC-A to include the country information.</li>
        <li>Used with consumer products internationally.</li>
        <li>Composed by 2 number system, 5 manufacturer code, 5 product code and 1 check digit.</li>
    </ul>
</div>

<?php
include('include/footer.php');
?>