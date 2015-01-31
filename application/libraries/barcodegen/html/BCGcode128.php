<?php
define('IN_CB', true);
include('include/header.php');

$default_value['start'] = '';
$start = isset($_POST['start']) ? $_POST['start'] : $default_value['start'];
registerImageKey('start', $start);
registerImageKey('code', 'BCGcode128');

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
</ul>

<div id="validCharacters">
    <h3>Valid Characters</h3>
    <?php $c = count($characters); for ($i = 0; $i < $c; $i++) { echo getButton($characters[$i], $vals[$i]); } ?>
</div>

<div id="explanation">
    <h3>Explanation</h3>
    <ul>
        <li>Code 128 is a high-density alphanumeric symbology.</li>
        <li>Used extensively worldwide.</li>
        <li>Code 128 is designed to encode 128 full ASCII characters.</li>
        <li>The symbology includes a checksum digit.</li>
        <li>Code 128A handles capital letters<br />Code 128B handles capital letters and lowercase<br />Code 128C handles group of 2 numbers</li>
        <li>Your browser may not be able to write the special characters (NUL, SOH, etc.) but you can write them with the code.</li>
    </ul>
</div>

<?php
include('include/footer.php');
?>