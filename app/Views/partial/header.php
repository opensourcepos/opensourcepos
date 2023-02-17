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
	<base href="<?php echo base_url() ?>" />
	<title><?php echo esc($config['company']) . ' | ' . lang('Common.powered_by') . ' OSPOS ' . esc(config('App')->application_version) ?></title>
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url() ?>favicon.ico">
	<link rel="stylesheet" type="text/css" href="<?php echo 'dist/bootswatch/' . (empty($config['theme']) ? 'flatly' : esc($config['theme'])) . '/bootstrap.min.css' ?>"/>

	<?php if (get_cookie('debug') == 'true' || $request->getGet('debug') == 'true') : ?>
		<!-- bower:css -->
		<link rel="stylesheet" href="bower_components/jquery-ui/themes/base/jquery-ui.css" />
		<link rel="stylesheet" href="bower_components/bootstrap3-dialog/dist/css/bootstrap-dialog.min.css" />
		<link rel="stylesheet" href="bower_components/jasny-bootstrap/dist/css/jasny-bootstrap.css" />
		<link rel="stylesheet" href="bower_components/smalot-bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
		<link rel="stylesheet" href="bower_components/bootstrap-select/dist/css/bootstrap-select.css" />
		<link rel="stylesheet" href="bower_components/bootstrap-table/dist/bootstrap-table.css" />
		<link rel="stylesheet" href="bower_components/bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.css" />
		<link rel="stylesheet" href="bower_components/bootstrap-daterangepicker/daterangepicker.css" />
		<link rel="stylesheet" href="bower_components/chartist/dist/chartist.min.css" />
		<link rel="stylesheet" href="bower_components/chartist-plugin-tooltip/dist/chartist-plugin-tooltip.css" />
		<link rel="stylesheet" href="bower_components/bootstrap-tagsinput/dist/bootstrap-tagsinput.css" />
		<link rel="stylesheet" href="bower_components/bootstrap-toggle/css/bootstrap-toggle.min.css" />
		<!-- endbower -->
		<!-- start css template tags -->
		<link rel="stylesheet" type="text/css" href="../../../public/css/bootstrap.autocomplete.css"/>
		<link rel="stylesheet" type="text/css" href="../../../public/css/invoice.css"/>
		<link rel="stylesheet" type="text/css" href="../../../public/css/ospos_print.css"/>
		<link rel="stylesheet" type="text/css" href="../../../public/css/ospos.css"/>
		<link rel="stylesheet" type="text/css" href="../../../public/css/popupbox.css"/>
		<link rel="stylesheet" type="text/css" href="../../../public/css/receipt.css"/>
		<link rel="stylesheet" type="text/css" href="../../../public/css/register.css"/>
		<link rel="stylesheet" type="text/css" href="../../../public/css/reports.css"/>
		<!-- end css template tags -->
		<!-- bower:js -->
		<script src="bower_components/jquery/dist/jquery.js"></script>
		<script src="bower_components/jquery-form/src/jquery.form.js"></script>
		<script src="bower_components/jquery-validate/dist/jquery.validate.js"></script>
		<script src="bower_components/jquery-ui/jquery-ui.js"></script>
		<script src="bower_components/bootstrap/dist/js/bootstrap.js"></script>
		<script src="bower_components/bootstrap3-dialog/dist/js/bootstrap-dialog.min.js"></script>
		<script src="bower_components/jasny-bootstrap/dist/js/jasny-bootstrap.js"></script>
		<script src="bower_components/smalot-bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
		<script src="bower_components/bootstrap-select/dist/js/bootstrap-select.js"></script>
		<script src="bower_components/bootstrap-table/dist/bootstrap-table.min.js"></script>
		<script src="bower_components/bootstrap-table/dist/extensions/export/bootstrap-table-export.min.js"></script>
		<script src="bower_components/bootstrap-table/dist/extensions/mobile/bootstrap-table-mobile.min.js"></script>
		<script src="bower_components/bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.min.js"></script>
		<script src="bower_components/moment/moment.js"></script>
		<script src="bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
		<script src="bower_components/es6-promise/es6-promise.js"></script>
		<script src="bower_components/file-saver/dist/FileSaver.min.js"></script>
		<script src="bower_components/html2canvas/build/html2canvas.js"></script>
		<script src="bower_components/jspdf/dist/jspdf.debug.js"></script>
		<script src="bower_components/jspdf-autotable/dist/jspdf.plugin.autotable.js"></script>
		<script src="bower_components/tableExport.jquery.plugin/tableExport.js"></script>
		<script src="bower_components/chartist/dist/chartist.min.js"></script>
		<script src="bower_components/chartist-plugin-pointlabels/dist/chartist-plugin-pointlabels.min.js"></script>
		<script src="bower_components/chartist-plugin-tooltip/dist/chartist-plugin-tooltip.min.js"></script>
		<script src="bower_components/chartist-plugin-barlabels/dist/chartist-plugin-barlabels.min.js"></script>
		<script src="bower_components/remarkable-bootstrap-notify/bootstrap-notify.js"></script>
		<script src="bower_components/js-cookie/src/js.cookie.js"></script>
		<script src="bower_components/bootstrap-tagsinput/dist/bootstrap-tagsinput.js"></script>
		<script src="bower_components/bootstrap-toggle/js/bootstrap-toggle.min.js"></script>
		<!-- endbower -->
		<!-- start js template tags -->
		<script type="text/javascript" src="../../../public/js/clipboard.min.js"></script>
		<script type="text/javascript" src="../../../public/js/imgpreview.full.jquery.js"></script>
		<script type="text/javascript" src="../../../public/js/manage_tables.js"></script>
		<script type="text/javascript" src="../../../public/js/nominatim.autocomplete.js"></script>
		<!-- end js template tags -->
	<?php else : ?>
		<!--[if lte IE 8]>
		<link rel="stylesheet" media="print" href="dist/print.css" type="text/css" />
		<![endif]-->
		<!-- start mincss template tags -->
		<link rel="stylesheet" type="text/css" href="../../../public/dist/jquery-ui/jquery-ui.min.css"/>
		<link rel="stylesheet" type="text/css" href="../../../public/dist/opensourcepos.min.css?rel=77a4efd3b0"/>
		<!-- end mincss template tags -->

		<!-- Tweaks to the UI for a particular theme should drop here  -->
	<?php if ($config['theme'] != 'flatly' && file_exists($_SERVER['DOCUMENT_ROOT'] . '/public/css/' . esc($config['theme']) . '.css')) { ?>
		<link rel="stylesheet" type="text/css" href="<?php echo 'css/' . esc($config['theme']) . '.css' ?>"/>
	<?php } ?>

		<!-- start minjs template tags -->
		<script type="text/javascript" src="../../../public/dist/opensourcepos.min.js?rel=16e6f23cde"></script>
		<!-- end minjs template tags -->
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
					<?= anchor(esc("home/change_password/$user_info->person_id", 'url'), esc("$user_info->first_name $user_info->last_name", 'attr'), ['class' => 'modal-dlg', 'data-btn-submit' => lang('Common.submit'), 'title' => lang('Employees.change_password')]) ?>
					<?= '  |  ' . ($request->getGet('debug') == 'true' ? session('session_sha1') . '  |  ' : '') ?>
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
