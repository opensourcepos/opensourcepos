<br>
<div class="container-fluid">
	<ul class="nav nav-tabs" id="myTabs" data-toggle="tab">
		<li class="active"><a href="#system_tabs" data-toggle="tab" title="<?php echo lang('Config.system_conf') ?>"><?php echo lang('Config.system_conf') ?></a></li>
		<li><a href="#email_tabs" data-toggle="tab" title="<?php echo lang('Config.email_configuration') ?>"><?php echo lang('Config.email') ?></a></li>
		<li><a href="#message_tabs" data-toggle="tab" title="<?php echo lang('Config.message_configuration') ?>"><?php echo lang('Config.message') ?></a></li>
		<li><a href="#integrations_tabs" data-toggle="tab" title="<?php echo lang('Config.integrations_configuration') ?>"><?php echo lang('Config.integrations') ?></a></li>
		<li><a href="#license_tabs" data-toggle="tab" title="<?php echo lang('Config.license_configuration') ?>"><?php echo lang('Config.license') ?></a></li>
	</ul>  
		<div class="tab-content">
		<div class="tab-pane active" id="system_tabs"><?php echo view('configs/system_info') ?></div>
		<div class="tab-pane" id="email_tabs"><?php echo view('configs/email_config') ?></div>
		<div class="tab-pane" id="message_tabs"><?php echo view('configs/message_config') ?></div>
		<div class="tab-pane" id="integrations_tabs"><?php echo view('configs/integrations_config') ?></div>
		<div class="tab-pane" id="license_tabs"><br><?php echo view('configs/license_config') ?></div>
	</div>
</div>
