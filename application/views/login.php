<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<base href="<?php echo base_url();?>" />
	<title>Open Source Point Of Sale <?php echo $this->lang->line('login_login'); ?></title>
	<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
	<link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="css/login.css" />

	<script type="text/javascript">
		$(document).ready(function()
		{
			$("#login_form input:first").focus();
		});
	</script>
</head>

<body>
	<div id="logo" align="center"><img src=<?php echo base_url();?>/images/logo.gif></div>

	<div id="login">
		<?php echo form_open('login') ?>
			<div id="container">
				<?php echo validation_errors(); ?>
				
				<div id="login_form">
					<div class="form_field_label"><?php echo $this->lang->line('login_username'); ?>: </div>
					<div class="form_field">
						<?php echo form_input(array(
						'name'=>'username',
						'id'=>'username',
						'size'=>'20')); ?>
					</div>

					<div class="form_field_label"><?php echo $this->lang->line('login_password'); ?>: </div>
					<div class="form_field">
						<?php echo form_password(array(
						'name'=>'password',
						'id' => 'password',
						'size'=>'20')); ?>
					</div>
					
					<input class="btn btn-primary btn-block" type="submit" name="loginButton" value="Go"/>
				</div>
			</div>
		<?php echo form_close(); ?>
		
		<h1>Open Source Point Of Sale <?php echo $this->config->item('application_version'); ?></h1>
	</div>
</body>
</html>
