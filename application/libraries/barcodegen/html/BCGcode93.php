<?php
define('IN_CB', true);
include('include/header.php');

registerImageKey('code', 'BCGcode93');

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

<div id="validCharacters">
    <h3>Valid Characters</h3>
    <?php $c = count($characters); for ($i = 0; $i < $c; $i++) { echo getButton($characters[$i], $vals[$i]); } ?>
</div>

<div id="explanation">
    <h3>Explanation</h3>
    <ul>
        <li>Known also as USS Code 93.</li>
        <li>Code 93 was designed to provide a higher density and data security enhancement to Code 39.</li>
        <li>Used primarily by Canadian postal office to encode supplementary delivery information.</li>
        <li>Similar to Code 39, Code 93 has the same 43 characters plus 5 special ones to encode the ASCII 0 to 127.</li>
        <li>This symbology composed of 2 check digits ("C" and "K").</li>
        <li>Your browser may not be able to write the special characters (NUL, SOH, etc.) but you can write them with the code.</li>
    </ul>
</div>

<?php
include('include/footer.php');
?>