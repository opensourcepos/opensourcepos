<br>
<div class="container-fluid">
	<ul class="nav nav-tabs" id="myTabs" data-toggle="tab">
		<li class="active"><a href="#system_tabs" data-toggle="tab" title="<?php echo $this->lang->line('config_system_conf'); ?>"><?php echo $this->lang->line('config_system_conf'); ?></a></li>
		<li><a href="#email_tabs" data-toggle="tab" title="<?php echo $this->lang->line('config_email_configuration'); ?>"><?php echo $this->lang->line('config_email'); ?></a></li>
		<li><a href="#message_tabs" data-toggle="tab" title="<?php echo $this->lang->line('config_message_configuration'); ?>"><?php echo $this->lang->line('config_message'); ?></a></li>
		<li><a href="#integrations_tabs" data-toggle="tab" title="<?php echo $this->lang->line('config_integrations_configuration'); ?>"><?php echo $this->lang->line('config_integrations'); ?></a></li>
		<li><a href="#license_tabs" data-toggle="tab" title="<?php echo $this->lang->line('config_license_configuration'); ?>"><?php echo $this->lang->line('config_license'); ?></a></li>
	</ul>  
		<div class="tab-content">
		<div class="tab-pane active" id="system_tabs"><?php $this->load->view("configs/system_info"); ?></div>
		<div class="tab-pane" id="email_tabs"><?php $this->load->view("configs/email_config"); ?></div>
		<div class="tab-pane" id="message_tabs"><?php $this->load->view("configs/message_config"); ?></div>
		<div class="tab-pane" id="integrations_tabs"><?php $this->load->view("configs/integrations_config"); ?></div>
		<div class="tab-pane" id="license_tabs"><br><?php $this->load->view("configs/license_config"); ?></div>
	</div>
</div>
