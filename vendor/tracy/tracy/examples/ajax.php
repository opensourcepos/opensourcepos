<?php

require __DIR__ . '/../src/tracy.php';

use Tracy\Debugger;

session_start(); // session is required for this functionality
Debugger::enable(Debugger::DETECT, __DIR__ . '/log');


if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) { // AJAX request
	Debugger::barDump('AJAX request');
	if (!empty($_GET['error'])) {
		this_is_fatal_error();
	}
	$data = [rand(), rand(), rand()];
	header('Content-Type: application/json');
	header('Cache-Control: no-cache');
	echo json_encode($data);
	exit;
}

Debugger::barDump('classic request');

?>
<!DOCTYPE html><html class=arrow><link rel="stylesheet" href="assets/style.css">

<h1>Tracy: AJAX demo</h1>

<p>
	<button>AJAX request</button> <span id=result>see Debug Bar in the bottom right corner</span>
</p>

<p>
	<button class=error>Request with error</button> use ESC to toggle BlueScreen
</p>


<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script>

var jqxhr;

$('button').click(function() {
	$('#result').text('loading…');

	if (jqxhr) {
		jqxhr.abort();
	}

	jqxhr = $.ajax({
		data: {error: $(this).hasClass('error') * 1},
		dataType: 'json',
		// headers: {'X-Tracy-Ajax': Tracy.getAjaxHeader()}, // use when auto-refresh is disabled via window.TracyAutoRefresh = false;
	}).done(function(data) {
		$('#result').text('loaded: ' + data);

	}).fail(function() {
		$('#result').text('error');
	});
});


</script>
