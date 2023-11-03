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
	<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
	<link rel="stylesheet" type="text/css" href="<?php echo 'resources/bootswatch/' . (empty($config['theme']) ? 'flatly' : esc($config['theme'])) . '/bootstrap.min.css' ?>"/>

	<?php if (ENVIRONMENT == 'development' || get_cookie('debug') == 'true' || $request->getGet('debug') == 'true') : ?>
		<!-- inject:debug:css -->
		<!-- endinject -->
		<!-- inject:debug:js -->
		<!-- endinject -->
	<?php else : ?>
		<!--inject:prod:css -->
		<!-- endinject -->

		<!-- Tweaks to the UI for a particular theme should drop here  -->
	<?php if ($config['theme'] != 'flatly' && file_exists($_SERVER['DOCUMENT_ROOT'] . '/public/css/' . esc($config['theme']) . '.css')) { ?>
		<link rel="stylesheet" type="text/css" href="<?php echo 'css/' . esc($config['theme']) . '.css' ?>"/>
	<?php } ?>
		<!-- inject:prod:js -->
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
