<?php
define('IN_CB', true);
include('include/header.php');

registerImageKey('code', 'BCGcode11');

$characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '-');
?>

<div id="validCharacters">
    <h3>Valid Characters</h3>
    <?php foreach ($characters as $character) { echo getButton($character); } ?>
</div>

<div id="explanation">
    <h3>Explanation</h3>
    <ul>
        <li>Known also as USD-8.</li>
        <li>Code 11 was developed in 1977 as a high-density numeric symbology.</li>
        <li>Used to identify telecommunications equipment.</li>
        <li>Code 11 is a numeric symbology and its character set consists of 10 digital characters and the dash symbol (-).</li>
        <li>There is a "C" check digit. If the length of encoded message is greater thant 10, a "K" check digit may be used.</li>
    </ul>
</div>

<?php
include('include/footer.php');
?>