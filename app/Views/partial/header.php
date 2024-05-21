<?php
/**
 * @var object $user_info
 * @var array $allowed_modules
 * @var CodeIgniter\HTTP\IncomingRequest $request
 * @var array $config
 */

use Config\Services;

$request = Services::request();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?= $request->getLocale() ?>">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<base href="<?= base_url() ?>" />
	<title><?= esc($config['company']) . ' | ' . lang('Common.powered_by') . ' OSPOS ' . esc(config('App')->application_version) ?></title>
	<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
	<link rel="stylesheet" type="text/css" href="<?= 'resources/bootswatch/' . (empty($config['theme']) ? 'flatly' : esc($config['theme'])) . '/bootstrap.min.css' ?>"/>

	<?php if (ENVIRONMENT == 'development' || get_cookie('debug') == 'true' || $request->getGet('debug') == 'true') : ?>
		<!-- inject:debug:css -->
		<link rel="stylesheet" href="resources/css/jquery-ui-fe010342cb.css">
		<link rel="stylesheet" href="resources/css/bootstrap-dialog-1716ef6e7c.css">
		<link rel="stylesheet" href="resources/css/jasny-bootstrap-40bf85f3ed.css">
		<link rel="stylesheet" href="resources/css/bootstrap-datetimepicker-66374fba71.css">
		<link rel="stylesheet" href="resources/css/bootstrap-select-66d5473b84.css">
		<link rel="stylesheet" href="resources/css/bootstrap-table-b8cfc92b6f.css">
		<link rel="stylesheet" href="resources/css/bootstrap-table-sticky-header-07d65e7533.css">
		<link rel="stylesheet" href="resources/css/daterangepicker-85523b7dfe.css">
		<link rel="stylesheet" href="resources/css/chartist-c19aedb81a.css">
		<link rel="stylesheet" href="resources/css/chartist-plugin-tooltip-2e0ec92e60.css">
		<link rel="stylesheet" href="resources/css/bootstrap-tagsinput-5a6d46a06c.css">
		<link rel="stylesheet" href="resources/css/bootstrap-toggle-e12db6c1f3.css">
		<link rel="stylesheet" href="resources/css/bootstrap-21011437af.autocomplete.css">
		<link rel="stylesheet" href="resources/css/invoice-2ec9a31990.css">
		<link rel="stylesheet" href="resources/css/ospos_print-ec2690e6fc.css">
		<link rel="stylesheet" href="resources/css/ospos-5822271440.css">
		<link rel="stylesheet" href="resources/css/popupbox-0db8527aa9.css">
		<link rel="stylesheet" href="resources/css/receipt-ad2a5392c5.css">
		<link rel="stylesheet" href="resources/css/register-517832340a.css">
		<link rel="stylesheet" href="resources/css/reports-872a457221.css">
		<!-- endinject -->
		<!-- inject:debug:js -->
		<script src="resources/js/jquery-12e87d2f3a.js"></script>
		<script src="resources/js/jquery-4fa896f615.form.js"></script>
		<script src="resources/js/jquery-a0350e8820.validate.js"></script>
		<script src="resources/js/jquery-ui-cbc65ff85e.js"></script>
		<script src="resources/js/bootstrap-894d79839f.js"></script>
		<script src="resources/js/bootstrap-dialog-27123abb65.js"></script>
		<script src="resources/js/jasny-bootstrap-7c6d7b8adf.js"></script>
		<script src="resources/js/bootstrap-datetimepicker-25e39b7ef8.js"></script>
		<script src="resources/js/bootstrap-select-b01896a67b.js"></script>
		<script src="resources/js/bootstrap-table-94d3527d23.js"></script>
		<script src="resources/js/bootstrap-table-export-00a4faa2f1.js"></script>
		<script src="resources/js/bootstrap-table-mobile-5df5626d47.js"></script>
		<script src="resources/js/bootstrap-table-sticky-header-e68e9836b4.js"></script>
		<script src="resources/js/moment-d65dc6d2e6.min.js"></script>
		<script src="resources/js/daterangepicker-048c56a690.js"></script>
		<script src="resources/js/es6-promise-855125e6f5.js"></script>
		<script src="resources/js/FileSaver-e73b1946e8.js"></script>
		<script src="resources/js/html2canvas-e1d3a8d7cd.js"></script>
		<script src="resources/js/jspdf-ff4663431d.umd.js"></script>
		<script src="resources/js/jspdf-8ce85cc4b6.plugin.autotable.js"></script>
		<script src="resources/js/tableExport-0df60917ca.min.js"></script>
		<script src="resources/js/chartist-8a7ecb4445.js"></script>
		<script src="resources/js/chartist-plugin-pointlabels-0a1ab6aa4e.js"></script>
		<script src="resources/js/chartist-plugin-tooltip-116cb48831.js"></script>
		<script src="resources/js/chartist-plugin-axistitle-80a1198058.js"></script>
		<script src="resources/js/chartist-plugin-barlabels-4165273742.js"></script>
		<script src="resources/js/bootstrap-notify-376bc6eb87.js"></script>
		<script src="resources/js/js-fa93e8894e.cookie.js"></script>
		<script src="resources/js/bootstrap-tagsinput-855a7c7670.js"></script>
		<script src="resources/js/bootstrap-toggle-1c7a19a049.js"></script>
		<script src="resources/js/clipboard-908af414ab.js"></script>
		<script src="resources/js/imgpreview-140c57d0ea.full.jquery.js"></script>
		<script src="resources/js/manage_tables-a3b0622cb7.js"></script>
		<script src="resources/js/nominatim-60392bf22a.autocomplete.js"></script>
		<!-- endinject -->
	<?php else : ?>
		<!--inject:prod:css -->
		<link rel="stylesheet" href="resources/opensourcepos-da76bf05c6.min.css">
		<!-- endinject -->

		<!-- Tweaks to the UI for a particular theme should drop here  -->
	<?php if ($config['theme'] != 'flatly' && file_exists($_SERVER['DOCUMENT_ROOT'] . '/public/css/' . esc($config['theme']) . '.css')) { ?>
		<link rel="stylesheet" type="text/css" href="<?= 'css/' . esc($config['theme']) . '.css' ?>"/>
	<?php } ?>
		<!-- inject:prod:js -->
		<script src="resources/jquery-2c872dbe60.min.js"></script>
		<script src="resources/opensourcepos-43589af889.min.js"></script>
		<!-- endinject -->
	<?php endif; ?>

	<?= view('partial/header_js') ?>
	<?= view('partial/lang_lines') ?>

	<style type="text/css">
		html {
			overflow: auto;
		}
	</style>
</head>

<body>
	<div class="wrapper">
		<div class="topbar">
			<div class="container">
				<div class="navbar-left">
					<div id="liveclock"><?= date($config['dateformat'] . ' ' . $config['timeformat']) ?></div>
				</div>

				<div class="navbar-right" style="margin:0">
					<?= anchor("home/changePassword/$user_info->person_id", "$user_info->first_name $user_info->last_name", ['class' => 'modal-dlg', 'data-btn-submit' => lang('Common.submit'), 'title' => lang('Employees.change_password')]) ?>
					<?= '  |  ' . ((ENVIRONMENT == 'development' || $request->getGet('debugdebug') == 'true') ? session('session_sha1') . '  |  ' : '') ?>
					<?= anchor('home/logout', lang('Login.logout')) ?>
				</div>

				<div class="navbar-center" style="text-align:center">
					<strong><?= esc($config['company']) ?></strong>
				</div>
			</div>
		</div>

		<div class="navbar navbar-default" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>

					<a class="navbar-brand hidden-sm" href="<?= site_url() ?>">OSPOS</a>
				</div>

				<div class="navbar-collapse collapse">
					<ul class="nav navbar-nav navbar-right">
						<?php foreach($allowed_modules as $module): ?>
							<li class="<?= $module->module_id == $request->getUri()->getSegment(1) ? 'active' : '' ?>">
								<a href="<?= base_url($module->module_id) ?>" title="<?= lang("Module.$module->module_id") ?>" class="menu-icon">

									<img src="<?= base_url("images/menubar/$module->module_id.png") ?>" style="border: none;" alt="Module Icon"/><br/>
									<?= lang('Module.' . $module->module_id) ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>

		<div class="container">
			<div class="row">
