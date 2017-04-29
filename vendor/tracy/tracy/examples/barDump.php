<?php

require __DIR__ . '/../src/tracy.php';

use Tracy\Debugger;

Debugger::enable(Debugger::DEVELOPMENT, __DIR__ . '/log');

?>
<!DOCTYPE html><html class=arrow><link rel="stylesheet" href="assets/style.css">

<h1>Tracy: bar dump demo</h1>

<p>You can dump variables to bar in rightmost bottom egde.</p>

<?php
$arr = [10, 20.2, TRUE, NULL, 'hello', (object) NULL, []];

bdump(get_defined_vars());

bdump($arr, 'The Array');

bdump('<a href="#">test</a>', 'String');
