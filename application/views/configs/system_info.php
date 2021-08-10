<?php
$logs = '../application/logs/';
$uploads = '../public/uploads/';
$images = '../public/uploads/item_pics/';
$import = '../import_items.csv';
$importcustomers = '../import_customers.csv';
$bullet = '&raquo; ';
$divider = ' &middot; ';
$enabled = '<span class="text-success">&#10003; Enabled</span>';
$disabled = '<span class="text-danger">&#10007; Disabled</span>';
$writable = '<span class="text-success">&#10003; Writable</span>';
$notwritable = '<span class="text-danger">&#10007; Not Writable</span>';
$readable = '<span class="text-success">&#10003; Readable</span>';
$notreadable = '<span class="text-danger">&#10007; Not Readable</span>';
$permissions_check = '<span class="text-success">&#10003; Security Check Passed</span>';
$permissions_fail = '<span class="text-danger">&#10007; Vulnerable or Incorrect Permissions</span>';

function get_browser_name($user_agent)
{
	$t = strtolower($user_agent);
	$t = " " . $t;
	if (strpos($t, 'opera') || strpos($t, 'opr/')) return 'Opera';
	elseif (strpos($t, 'edge')) return 'Edge';
	elseif (strpos($t, 'chrome')) return 'Chrome';
	elseif (strpos($t, 'safari')) return 'Safari';
	elseif (strpos($t, 'firefox')) return 'Firefox';
	elseif (strpos($t, 'msie') || strpos($t, 'trident/7')) return 'Internet Explorer';
	return 'Unknown';
}
?>

<?php
$title_system['config_title'] = $this->lang->line('config_system_info');
$this->load->view('configs/config_header', $title_system);
?>

<div class="mb-3"><?= $this->lang->line('config_server_notice'); ?></div>

<form id="copy-issue">

	<?php
	if (!((substr(decoct(fileperms($logs)), -4) == 750) && (substr(decoct(fileperms($uploads)), -4) == 750) && (substr(decoct(fileperms($images)), -4) == 750) && ((substr(decoct(fileperms($importcustomers)), -4) == 640) || (substr(decoct(fileperms($importcustomers)), -4) == 660)))) {
		echo '<div class="card text-white bg-danger mb-4">
		<div class="card-header fw-bold"><i class="bi bi-exclamation-circle"></i> ' . $this->lang->line('config_security_issue') . '</div>
			<div class="card-body">
				<p class="card-text">' . $this->lang->line('config_perm_risk') . '</p>
				<ul class="list-unstyled mb-0">';
		if (substr(decoct(fileperms($logs)), -4) != 750) {
			echo '<li class="card-text">' . $bullet . '<code class="text-white">application/logs</code> ' . $this->lang->line('config_is_writable') . '</li>';
		}
		if (substr(decoct(fileperms($uploads)), -4) != 750) {
			echo '<li class="card-text">' . $bullet . '<code class="text-white">public/uploads</code> ' . $this->lang->line('config_is_writable') . '</li>';
		}
		if (substr(decoct(fileperms($images)), -4) != 750) {
			echo '<li class="card-text">' . $bullet . '<code class="text-white">public/uploads/item_pics</code> ' . $this->lang->line('config_is_writable') . '</li>';
		}
		if (!((substr(decoct(fileperms($importcustomers)), -4) == 640) || (substr(decoct(fileperms($importcustomers)), -4) == 660))) {
			echo '<li class="card-text">' . $bullet . '<code class="text-white">import_customers.csv</code> ' . $this->lang->line('config_is_readable') . '</li>';
		}
		echo '</div></div>';
	}
	?>

	<div class="row mb-3">
		<label for="general-info" class="col-12 col-lg-2 form-label fw-bold">General Info</label>
		<div class="col-12 col-lg-10" id="general-info">
			<?= $this->lang->line('config_ospos_info') . ': ' . $this->config->item('application_version') . ' - ' . substr($this->config->item('commit_sha1'), 0, 6); ?><br>
			<div>Language Code: <?= current_language_code(); ?></div><br>
			<div id="time-error" class="row mb-3 d-none">
				<div class="col-12 text-danger"><?= $this->lang->line('config_timezone_error'); ?></div>
				<div class="col-6">
					<label for="timezone"><?= $this->lang->line('config_user_timezone'); ?></label>
					<div id="timezone"></div>
				</div>
				<div class="col-6">
					<label for="ostimezone"><?= $this->lang->line('config_os_timezone'); ?></label>
					<div id="ostimezone"><?= $this->config->item('timezone'); ?></div>
				</div>
			</div>
			<span>Extensions & Modules:</span><br>
			<ul class="list-unstyled">
				<li><?= $bullet . 'GD: ', extension_loaded('gd') ? $enabled : $disabled; ?></li>
				<li><?= $bullet . 'BC Math: ', extension_loaded('bcmath') ? $enabled : $disabled; ?></li>
				<li><?= $bullet . 'INTL: ', extension_loaded('intl') ? $enabled : $disabled; ?></li>
				<li><?= $bullet . 'OpenSSL: ', extension_loaded('openssl') ? $enabled : $disabled; ?></li>
				<li><?= $bullet . 'MBString: ', extension_loaded('mbstring') ? $enabled : $disabled; ?></li>
				<li><?= $bullet . 'Curl: ', extension_loaded('curl') ? $enabled : $disabled; ?></li>
			</ul>
		</div>
	</div>

	<div class="row mb-3">
		<label for="user-setup" class="col-12 col-lg-2 form-label fw-bold">User Setup</label>
		<div class="col-12 col-lg-10" id="user-setup">
			<ul class="list-unstyled">
				<li><?= $bullet . 'Browser: ' . get_browser_name($_SERVER['HTTP_USER_AGENT']); ?></li>
				<li><?= $bullet . 'Server Software: ' . $_SERVER['SERVER_SOFTWARE']; ?></li>
				<li><?= $bullet . 'PHP Version: ' . PHP_VERSION; ?></li>
				<li><?= $bullet . 'DB Version: ' . mysqli_get_server_info($this->db->conn_id); ?></li>
				<li><?= $bullet . 'Server Port: ' . $_SERVER['SERVER_PORT']; ?></li>
				<li><?= $bullet . 'OS: ' . php_uname('s') . ' ' . php_uname('r'); ?></li>
			</ul>
		</div>
	</div>

	<div class="row mb-3">
		<label for="permissions" class="col-12 col-lg-2 form-label fw-bold">Permissions</label>
		<div class="col-12 col-lg-10" id="permissions">
			<ul class="list-unstyled">
				<li>
					<?= $bullet; ?><code>application/logs</code>
					<?php
					if (is_writable($logs)) {
						echo substr(sprintf("%o", fileperms($logs)), -4) . $divider . $writable;
					} else {
						echo substr(sprintf("%o", fileperms($logs)), -4) . $notwritable;
					}
					clearstatcache();
					echo $divider;
					if (is_writable($logs) && substr(decoct(fileperms($logs)), -4) != 750) {
						echo $permissions_fail;
					} else {
						echo $permissions_check;
					}
					clearstatcache();
					?>
				</li>
				<li>
					<?= $bullet; ?><code>public/uploads</code>
					<?php
					if (is_writable($uploads)) {
						echo substr(sprintf("%o", fileperms($uploads)), -4) . $divider . $writable;
					} else {
						echo substr(sprintf("%o", fileperms($uploads)), -4) . $notwritable;
					}
					clearstatcache();
					echo $divider;
					if (is_writable($uploads) && substr(decoct(fileperms($uploads)), -4) != 750) {
						echo $permissions_fail;
					} else {
						echo $permissions_check;
					}
					clearstatcache();
					?>
				</li>
				<li>
					<?= $bullet; ?><code>public/uploads/item_pics</code>
					<?php
					if (is_writable($images)) {
						echo substr(sprintf("%o", fileperms($images)), -4) . $divider . $writable;
					} else {
						echo substr(sprintf("%o", fileperms($images)), -4) . $notwritable;
					}
					clearstatcache();
					echo $divider;
					if (substr(decoct(fileperms($images)), -4) != 750) {
						echo $permissions_fail;
					} else {
						echo $permissions_check;
					}
					clearstatcache();
					?>
				</li>
				<li>
					<?= $bullet; ?><code>import_customers.csv</code>
					<?php
					if (is_readable($importcustomers)) {
						echo substr(sprintf("%o", fileperms($importcustomers)), -4) . $divider . $readable;
					} else {
						echo substr(sprintf("%o", fileperms($importcustomers)), -4) . $notreadable;
					}
					clearstatcache();
					echo $divider;
					if (!((substr(decoct(fileperms($importcustomers)), -4) == 640) || (substr(decoct(fileperms($importcustomers)), -4) == 660))) {
						echo $permissions_fail;
					} else {
						echo $permissions_check;
					}
					clearstatcache();
					?>
				</li>
			</ul>
			<?php
			if (((substr(decoct(fileperms($logs)), -4) == 750) && (substr(decoct(fileperms($uploads)), -4) == 750) && (substr(decoct(fileperms($images)), -4) == 750) && ((substr(decoct(fileperms($importcustomers)), -4) == 640) || (substr(decoct(fileperms($importcustomers)), -4) == 660)))) {
				echo '<span class="text-success">' . $this->lang->line('config_no_risk') . '</span>';
			} ?>
		</div>
	</div>
</form>

<div class="d-flex justify-content-center gap-3">
	<button class="copy btn btn-secondary" data-clipboard-action="copy" data-clipboard-target="#copy-issue"><i class="bi bi-clipboard-plus"></i> Copy Info</button>
	<a class="btn btn-secondary" href="https://github.com/opensourcepos/opensourcepos/issues/new" target="_blank" rel="noopener"><i class="bi bi-flag"></i> <?= $this->lang->line('config_report_an_issue'); ?></a>
</div>

<script type="text/javascript" src="dist/clipboard/clipboard.min.js"></script>

<script type="text/javascript">
	// clipboard.js
	var clipboard = new ClipboardJS('.copy');

	clipboard.on('success', function(e) {
		document.getSelection().removeAllRanges();
	});

	// timezone
	document.getElementById('timezone').innerText = Intl.DateTimeFormat().resolvedOptions().timeZone;

	var timezone = document.getElementById('timezone').innerText;
	var ostimezone = document.getElementById('ostimezone').innerText;

	if (timezone !== ostimezone) {
		document.getElementById('time-error').classList.remove('d-none');
	}
</script>