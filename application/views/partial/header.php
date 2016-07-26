<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<base href="<?php echo base_url();?>" />
	<title><?php echo $this->config->item('company') . ' | ' . $this->lang->line('common_powered_by') . ' OSPOS ' . $this->config->item('application_version') ?></title>
	<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
	<?php if ($this->input->cookie('debug') == "true" || $this->input->get("debug") == "true") : ?>
		<?php $this->load->view('partial/header_debug'); ?>
	<?php else : ?>
		<!--[if lte IE 8]>
		<link rel="stylesheet" media="print" href="css/print.css" type="text/css" />
		<![endif]-->
		<!-- start mincss template tags -->
		<link rel="stylesheet" type="text/css" href="dist/bootstrap.min.css?rel=0ff26f4768"/>
		<link rel="stylesheet" type="text/css" href="dist/jquery-ui.css"/>
		<link rel="stylesheet" type="text/css" href="dist/opensourcepos.min.css?rel=0ad5e454a4"/>
		<!-- end mincss template tags -->
		<!-- start minjs template tags -->
		<script type="text/javascript" src="dist/opensourcepos.min.js?rel=52a2324353"></script>
		<!-- end minjs template tags -->
	<?php endif; ?>

	<?php $this->load->view('partial/lang_lines'); ?>
	<?php $this->load->view('partial/header_js'); ?>

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
					<div id="liveclock"><?php echo date($this->config->item('dateformat') . ' ' . $this->config->item('timeformat')) ?></div>
				</div>
				
				<div class="navbar-right" style="margin:0">
					<?php echo $this->config->item('company') . "  |  $user_info->first_name $user_info->last_name  |  "; ?>
					<?php echo anchor("home/logout", $this->lang->line("common_logout")); ?>
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
			
					<a class="navbar-brand hidden-sm" href="<?php echo site_url(); ?>">OSPOS</a>
				</div>

				<div class="navbar-collapse collapse">
					<ul class="nav navbar-nav navbar-right">
						<?php foreach($allowed_modules->result() as $module): ?>
						<li class="<?php echo $module->module_id == $this->uri->segment(1)? 'active': ''; ?>">
							<a href="<?php echo site_url("$module->module_id");?>" title="<?php echo $this->lang->line("module_".$module->module_id);?>" class="menu-icon">
								<img src="<?php echo base_url().'images/menubar/'.$module->module_id.'.png';?>" border="0" alt="Module Icon" /><br />
								<?php echo $this->lang->line("module_".$module->module_id) ?>
							</a>
						</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>

		<div class="container">
			<div class="row">
	 
