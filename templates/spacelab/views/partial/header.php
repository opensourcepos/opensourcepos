<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<base href="<?php echo base_url();?>" />
	<title><?php echo $this->config->item('company').' -- '.$this->lang->line('common_powered_by').' OS Point Of Sale' ?></title>
	<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
	<link rel="stylesheet" type="text/css" href="css/ospos.css"/>
	<link rel="stylesheet" type="text/css" href="css/ospos_print.css" media="print" />
	<link rel="stylesheet" type="text/css" href="templates/spacelab/css/bootstrap.css"/>
	<link rel="stylesheet" type="text/css" href="templates/spacelab/css/style.css"/>

	<?php if ($this->input->cookie('debug') == "true" || $this->input->get("debug") == "true") : ?>
		<!-- start js template tags -->
		<script type="text/javascript" src="js/jquery-1.8.3.js" language="javascript"></script>
		<script type="text/javascript" src="js/jquery-ui-1.11.4.js" language="javascript"></script>
		<script type="text/javascript" src="js/jquery-ui-timepicker-addon.js" language="javascript"></script>
		<script type="text/javascript" src="js/jquery.ajax_queue.js" language="javascript"></script>
		<script type="text/javascript" src="js/jquery.autocomplete.js" language="javascript"></script>
		<script type="text/javascript" src="js/jquery.bgiframe.min.js" language="javascript"></script>
		<script type="text/javascript" src="js/jquery.color.js" language="javascript"></script>
		<script type="text/javascript" src="js/jquery.form-3.51.js" language="javascript"></script>
		<script type="text/javascript" src="js/jquery.jkey-1.1.js" language="javascript"></script>
		<script type="text/javascript" src="js/jquery.metadata.js" language="javascript"></script>
		<script type="text/javascript" src="js/jquery.tablesorter-2.20.1.js" language="javascript"></script>
		<script type="text/javascript" src="js/jquery.tablesorter.staticrow.js" language="javascript"></script>
		<script type="text/javascript" src="js/jquery.validate-1.13.1-min.js" language="javascript"></script>
		<script type="text/javascript" src="js/common.js" language="javascript"></script>
		<script type="text/javascript" src="js/date.js" language="javascript"></script>
		<script type="text/javascript" src="js/imgpreview.full.jquery.js" language="javascript"></script>
		<script type="text/javascript" src="js/manage_tables.js" language="javascript"></script>
		<script type="text/javascript" src="js/nominatim.autocomplete.js" language="javascript"></script>
		<script type="text/javascript" src="js/phpjsdate.js" language="javascript"></script>
		<script type="text/javascript" src="js/swfobject.js" language="javascript"></script>
		<script type="text/javascript" src="js/tabcontent.js" language="javascript"></script>
		<script type="text/javascript" src="js/thickbox.js" language="javascript"></script>
		<!-- end js template tags -->
	<?php else : ?>
		<!-- start minjs template tags -->
		<script type="text/javascript" src="dist/opensourcepos.min.js?rel=45f4375544" language="javascript"></script>
		<!-- end minjs template tags -->       
	<?php endif; ?>

	<script type="text/javascript">
		function logout(logout)
		{
			logout = logout && <?php echo $backup_allowed;?>;
			if (logout && confirm("<?php echo $this->lang->line('config_logout'); ?>"))
			{
				window.location = "<?php echo site_url('config/backup_db'); ?>";
			}
			else
			{
				window.location = "<?php echo site_url('home/logout'); ?>";
			}
		}
		
		// live clock
	
		function clockTick(){  
			setInterval('updateClock();', 1000);  
		}

		// start the clock immediatly
		clockTick();

		var now = new Date(<?php echo time() * 1000 ?>);

		function updateClock() {
			now.setTime(now.getTime() + 1000);
			
			document.getElementById('liveclock').innerHTML = phpjsDate("<?php echo $this->config->item('dateformat').' '.$this->config->item('timeformat') ?>", now);
		}
	</script>

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
				<div id="liveclock"><?php echo date($this->config->item('dateformat').' '.$this->config->item('timeformat')) ?></div>
			</div>
			<div class="navbar-right" style="margin:0">
				<?php echo $this->lang->line('common_welcome')." $user_info->first_name $user_info->last_name! | "; ?>
				<a href="javascript:logout(true);"><?php echo $this->lang->line("common_logout"); ?></a> 
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

			<div class="collapse navbar-collapse">
				<ul class="nav navbar-nav navbar-right">
					<?php foreach($allowed_modules->result() as $module): ?>
					<li class="<?php echo $module->module_id == $this->uri->segment(1)? 'active': ''; ?>">
						<a href="<?php echo site_url("$module->module_id");?>" title="<?php echo $this->lang->line("module_".$module->module_id) ?>" class="menu-icon">
							<img src="<?php echo base_url().'images/menubar/'.$module->module_id.'.png';?>" border="0" alt="Module Icon" /><br />
							<?php echo $this->lang->line("module_".$module->module_id) ?>
						</a>
					</li>
					<?php endforeach; ?>
				</ul>
			</div><!--/.nav-collapse -->
		</div>
	</div>

	<div class="container">
		<div class="row">
 
