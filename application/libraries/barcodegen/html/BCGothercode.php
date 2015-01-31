<?php
define('IN_CB', true);
include('include/header.php');

$default_value['label'] = '';
$label = isset($_POST['label']) ? $_POST['label'] : $default_value['label'];
registerImageKey('label', $label);
registerImageKey('code', 'BCGothercode');

$characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
?>

<ul id="specificOptions">
    <li class="option">
        <div class="title">
            <label for="label">Label</label>
        </div>
        <div class="value">
            <?php echo getInputTextHtml('label', $label); ?>
        </div>
    </li>
</ul>

<div id="validCharacters">
    <h3>Valid Characters</h3>
    <?php foreach ($characters as $character) { echo getButton($character); } ?>
</div>

<div id="explanation">
    <h3>Explanation</h3>
    <ul>
        <li>Enter width of each bars with one characters. Begin by a bar.</li>
        <li>10523: Will do 2px bar, 1px space, 6px bar, 3px space, 4px bar.</li>
    </ul>
</div>

<?php
include('include/footer.php');
?>
