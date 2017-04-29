<?php

require __DIR__ . '/../src/tracy.php';

use Tracy\Debugger;

Debugger::enable(Debugger::DETECT, __DIR__ . '/log');
Debugger::$strictMode = TRUE;

?>
<!DOCTYPE html><link rel="stylesheet" href="assets/style.css">

<h1>Tracy Warning and StrictMode demo</h1>

<?php

$f = fopen('nonexistent', 'r');
