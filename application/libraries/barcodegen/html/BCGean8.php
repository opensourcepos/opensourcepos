<?php
define('IN_CB', true);
include('include/header.php');

registerImageKey('code', 'BCGean8');

$characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
?>

<div id="validCharacters">
    <h3>Valid Characters</h3>
    <?php foreach ($characters as $character) { echo getButton($character); } ?>
</div>

<div id="explanation">
    <h3>Explanation</h3>
    <ul>
        <li>EAN-8 is a short version of EAN-13.</li>
        <li>Composed by 7 digits and 1 check digit.</li>
        <li>There is no conversion available between EAN-8 and EAN-13.</li>
    </ul>
</div>

<?php
include('include/footer.php');
?>