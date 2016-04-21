<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<base href="<?php echo base_url();?>" />
	<title><?php echo $this->config->item('company').' -- '.$this->lang->line('common_powered_by').' OS Point Of Sale' ?></title>
	<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
	<?php if ($this->input->cookie('debug') == "true" || $this->input->get("debug") == "true") : ?>
		<!-- bower:css -->
		<link rel="stylesheet" href="bower_components/jquery-ui/themes/base/jquery-ui.css" />
		<link rel="stylesheet" href="bower_components/tablesorter/dist/css/theme.blue.min.css" />
		<link rel="stylesheet" href="bower_components/bootstrap3-dialog/dist/css/bootstrap-dialog.min.css" />
		<link rel="stylesheet" href="bower_components/jasny-bootstrap/dist/css/jasny-bootstrap.css" />
		<link rel="stylesheet" href="bower_components/bootswatch-dist/css/bootstrap.css" />
		<link rel="stylesheet" href="bower_components/smalot-bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
		<link rel="stylesheet" href="bower_components/bootstrap-select/dist/css/bootstrap-select.css" />
		<link rel="stylesheet" href="bower_components/bootstrap-table/src/bootstrap-table.css" />
		<link rel="stylesheet" href="bower_components/bootstrap-daterangepicker/daterangepicker.css" />
		<!-- endbower -->
		<!-- start css template tags -->
		<link rel="stylesheet" type="text/css" href="css/barcode_font.css"/>
		<link rel="stylesheet" type="text/css" href="css/bootstrap.autocomplete.css"/>
		<link rel="stylesheet" type="text/css" href="css/invoice.css"/>
		<link rel="stylesheet" type="text/css" href="css/ospos.css"/>
		<link rel="stylesheet" type="text/css" href="css/ospos_print.css"/>
		<link rel="stylesheet" type="text/css" href="css/popupbox.css"/>
		<link rel="stylesheet" type="text/css" href="css/receipt.css"/>
		<link rel="stylesheet" type="text/css" href="css/register.css"/>
		<link rel="stylesheet" type="text/css" href="css/reports.css"/>
		<link rel="stylesheet" type="text/css" href="css/tables.css"/>
		<!-- end css template tags -->
		<!-- bower:js -->
		<script src="bower_components/jquery/dist/jquery.js"></script>
		<script src="bower_components/jquery-form/jquery.form.js"></script>
		<script src="bower_components/jquery-validate/dist/jquery.validate.js"></script>
		<script src="bower_components/jquery-ui/jquery-ui.js"></script>
		<script src="bower_components/swfobject-bower/swfobject/swfobject.js"></script>
		<script src="bower_components/tablesorter/dist/js/jquery.tablesorter.combined.js"></script>
		<script src="bower_components/bootstrap/dist/js/bootstrap.js"></script>
		<script src="bower_components/bootstrap3-dialog/dist/js/bootstrap-dialog.min.js"></script>
		<script src="bower_components/jasny-bootstrap/dist/js/jasny-bootstrap.js"></script>
		<script src="bower_components/bootswatch-dist/js/bootstrap.js"></script>
		<script src="bower_components/smalot-bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
		<script src="bower_components/bootstrap-select/dist/js/bootstrap-select.js"></script>
		<script src="bower_components/bootstrap-table/src/bootstrap-table.js"></script>
		<script src="bower_components/moment/moment.js"></script>
		<script src="bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
		<!-- endbower -->
		<!-- start js template tags -->
		<script type="text/javascript" src="js/jquery.tablesorter.staticrow.js" language="javascript"></script>
		<script type="text/javascript" src="js/common.js" language="javascript"></script>
		<script type="text/javascript" src="js/imgpreview.full.jquery.js" language="javascript"></script>
		<script type="text/javascript" src="js/manage_tables.js" language="javascript"></script>
		<script type="text/javascript" src="js/nominatim.autocomplete.js" language="javascript"></script>
		<script type="text/javascript" src="js/phpjsdate.js" language="javascript"></script>
		<!-- end js template tags -->
	<?php else : ?>
		<!--[if lte IE 8]>
		<link rel="stylesheet" media="print" href="css/print.css" type="text/css" />
		<![endif]-->
		<link rel="stylesheet" type="text/css" href="templates/spacelab/css/bootstrap.min.css?rel=9ed20b1ee8"/>
		<!-- start mincss template tags -->
		<link rel="stylesheet" type="text/css" href="dist/jquery-ui.css"/>
		<link rel="stylesheet" type="text/css" href="dist/opensourcepos.min.css?rel=208d2df00e"/>
		<!-- end mincss template tags -->
		<link rel="stylesheet" type="text/css" href="templates/spacelab/css/style.css"/>
		<!-- start minjs template tags -->
		<script type="text/javascript" src="dist/opensourcepos.min.js?rel=3d8bf015a8" language="javascript"></script>
		<!-- end minjs template tags -->
	<?php endif; ?>

	<script type="text/javascript">
		// live clock
	
		function clockTick() {  
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
		
				<a class="navbar-brand hidden-sm" href="<?php echo site_url();?>">OSPOS</a>
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
 
