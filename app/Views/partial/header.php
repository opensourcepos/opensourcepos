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
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo $request->getLocale() ?>">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<base href="<?php echo base_url() . '/' ?>" />
	<title><?php echo esc($config['company']) . ' | ' . lang('Common.powered_by') . ' OSPOS ' . esc(config('App')->application_version) ?></title>
	<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
	<link rel="stylesheet" type="text/css" href="<?php echo 'resources/bootswatch/' . (empty($config['theme']) ? 'flatly' : esc($config['theme'])) . '/bootstrap.min.css' ?>"/>

	<?php if (ENVIRONMENT == 'development' || get_cookie('debug') == 'true' || $request->getGet('debug') == 'true') : ?>
		<!-- inject:debug:css -->
		<link rel="stylesheet" href="resources/css/jquery-ui-6fd5a6e819.css">
		<link rel="stylesheet" href="resources/css/bootstrap-dialog-1716ef6e7c.css">
		<link rel="stylesheet" href="resources/css/jasny-bootstrap-40bf85f3ed.css">
		<link rel="stylesheet" href="resources/css/bootstrap-datetimepicker-6be929e975.css">
		<link rel="stylesheet" href="resources/css/bootstrap-select-66d5473b84.css">
		<link rel="stylesheet" href="resources/css/bootstrap-table-4b619bcd8f.css">
		<link rel="stylesheet" href="resources/css/bootstrap-table-sticky-header-07d65e7533.css">
		<link rel="stylesheet" href="resources/css/daterangepicker-85523b7dfe.css">
		<link rel="stylesheet" href="resources/css/chartist-c19aedb81a.css">
		<link rel="stylesheet" href="resources/css/chartist-plugin-tooltip-2e0ec92e60.css">
		<link rel="stylesheet" href="resources/css/bootstrap-tagsinput-01a1887ade.css">
		<link rel="stylesheet" href="resources/css/bootstrap-toggle-e12db6c1f3.css">
		<link rel="stylesheet" href="resources/css/bootstrap-019ef57791.autocomplete.css">
		<link rel="stylesheet" href="resources/css/invoice-6a526688bd.css">
		<link rel="stylesheet" href="resources/css/ospos_print-ad4fa36376.css">
		<link rel="stylesheet" href="resources/css/ospos-5ccf48a62d.css">
		<link rel="stylesheet" href="resources/css/popupbox-df1682d394.css">
		<link rel="stylesheet" href="resources/css/receipt-c2c74c776e.css">
		<link rel="stylesheet" href="resources/css/register-84b1267d84.css">
		<link rel="stylesheet" href="resources/css/reports-4b8616a379.css">
		<!-- endinject -->
		<!-- inject:debug:js -->
		<script src="resources/js/jquery-107fbe9555.js"></script>
		<script src="resources/js/jquery-4fa896f615.form.js"></script>
		<script src="resources/js/jquery-272ed07e41.validate.js"></script>
		<script src="resources/js/jquery-ui-ab5284de5e.js"></script>
		<script src="resources/js/bootstrap-894d79839f.js"></script>
		<script src="resources/js/bootstrap-dialog-27123abb65.js"></script>
		<script src="resources/js/jasny-bootstrap-7c6d7b8adf.js"></script>
		<script src="resources/js/bootstrap-datetimepicker-9ae21e5aa6.js"></script>
		<script src="resources/js/bootstrap-select-b01896a67b.js"></script>
		<script src="resources/js/bootstrap-table-63b5d3c3e6.js"></script>
		<script src="resources/js/bootstrap-table-export-e43b035e30.js"></script>
		<script src="resources/js/bootstrap-table-mobile-a8fb9324f4.js"></script>
		<script src="resources/js/bootstrap-table-sticky-header-7b4bb951c4.js"></script>
		<script src="resources/js/moment-6c0a2330b0.min.js"></script>
		<script src="resources/js/daterangepicker-2bb3f09fd8.js"></script>
		<script src="resources/js/es6-promise-855125e6f5.js"></script>
		<script src="resources/js/FileSaver-e73b1946e8.js"></script>
		<script src="resources/js/html2canvas-e1d3a8d7cd.js"></script>
		<script src="resources/js/jspdf-a03c76b858.debug.js"></script>
		<script src="resources/js/jspdf.plugin-b7d17cf8db.autotable.src.js"></script>
		<script src="resources/js/tableExport-8fad1e1d0f.min.js"></script>
		<script src="resources/js/chartist-8a7ecb4445.js"></script>
		<script src="resources/js/chartist-plugin-pointlabels-bc4349d572.js"></script>
		<script src="resources/js/chartist-plugin-tooltip-9c9958544c.js"></script>
		<script src="resources/js/chartist-plugin-barlabels-4165273742.js"></script>
		<script src="resources/js/bootstrap-notify-376bc6eb87.js"></script>
		<script src="resources/js/js-fa93e8894e.cookie.js"></script>
		<script src="resources/js/bootstrap-tagsinput-9d43ec6292.js"></script>
		<script src="resources/js/bootstrap-toggle-1c7a19a049.js"></script>
		<script src="resources/js/clipboard-908af414ab.js"></script>
		<script src="resources/js/imgpreview-4836346e15.full.jquery.js"></script>
		<script src="resources/js/manage_tables-e48b5201f8.js"></script>
		<script src="resources/js/nominatim-0f6e7d2e69.autocomplete.js"></script>
		<!-- endinject -->
	<?php else : ?>
		<!--inject:prod:css -->
		<link rel="stylesheet" href="resources/opensourcepos-215ec0f7db.min.css">
		<!-- endinject -->

		<!-- Tweaks to the UI for a particular theme should drop here  -->
	<?php if ($config['theme'] != 'flatly' && file_exists($_SERVER['DOCUMENT_ROOT'] . '/public/css/' . esc($config['theme']) . '.css')) { ?>
		<link rel="stylesheet" type="text/css" href="<?php echo 'css/' . esc($config['theme']) . '.css' ?>"/>
	<?php } ?>
		<!-- inject:prod:js -->
		<script src="resources/jquery-4a356126b9.min.js"></script>
		<script src="resources/opensourcepos-086e360103.min.js"></script>
		<!-- endinject -->
	<?php endif; ?>

	<?php echo view('partial/header_js') ?>
	<?php echo view('partial/lang_lines') ?>

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
					<?= anchor(esc("home/change_password/$user_info->person_id", 'url'), esc("$user_info->first_name $user_info->last_name"), ['class' => 'modal-dlg', 'data-btn-submit' => lang('Common.submit'), 'title' => lang('Employees.change_password')]) ?>
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
							<li class="<?= $module->module_id == $request->getUri()->getSegment(0) ? 'active' : '' ?>">
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
