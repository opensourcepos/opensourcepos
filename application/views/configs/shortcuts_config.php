<br>
<?php echo form_open('config/save_shortcuts/', array('id' => 'save_shortcuts', 'class' => 'form-horizontal')); ?>
	<div id="config_wrapper">
		<fieldset id="config_info">
			<ul id="shortcuts_error_message_box" class="error_message_box"></ul>
				<div class="form-group form-group-sm col-xs-7">
					<?php echo form_label($this->lang->line('sales_key_cancel'), 'key_cancel', array('class' => 'control-label col-xs-5')); ?>
					<div class='col-xs-4'>
						<?php echo form_dropdown('key_cancel', $key_shortcuts_options, $this->config->item('key_cancel'), array('class' => 'form-control input-sm')); ?>
					</div>
				</div>
				<div class="form-group form-group-sm col-xs-7">
					<?php echo form_label($this->lang->line('sales_key_item_search'), 'key_items', array('class' => 'control-label col-xs-5')); ?>
					<div class='col-xs-4'>
						<?php echo form_dropdown('key_items', $key_shortcuts_options, $this->config->item('key_items'), array('class' => 'form-control input-sm')); ?>
					</div>
				</div>
				<div class="form-group form-group-sm col-xs-7">
					<?php echo form_label($this->lang->line('sales_key_customer_search'), 'key_customers', array('class' => 'control-label col-xs-5')); ?>
					<div class='col-xs-4'>
						<?php echo form_dropdown('key_customers', $key_shortcuts_options, $this->config->item('key_customers'), array('class' => 'form-control input-sm')); ?>
					</div>
				</div>
				<div class="form-group form-group-sm col-xs-7">
					<?php echo form_label($this->lang->line('sales_key_suspend'), 'key_suspend', array('class' => 'control-label col-xs-5')); ?>
					<div class='col-xs-4'>
						<?php echo form_dropdown('key_suspend', $key_shortcuts_options, $this->config->item('key_suspend'), array('class' => 'form-control input-sm')); ?>
					</div>
				</div>
				<div class="form-group form-group-sm col-xs-7">
					<?php echo form_label($this->lang->line('sales_key_suspended'), 'key_suspended', array('class' => 'control-label col-xs-5')); ?>
					<div class='col-xs-4'>
						<?php echo form_dropdown('key_suspended', $key_shortcuts_options, $this->config->item('key_suspended'), array('class' => 'form-control input-sm')); ?>
					</div>
				</div>
				<div class="form-group form-group-sm col-xs-7">
					<?php echo form_label($this->lang->line('sales_key_tendered'), 'key_amount', array('class' => 'control-label col-xs-5')); ?>
					<div class='col-xs-4'>
						<?php echo form_dropdown('key_amount', $key_shortcuts_options, $this->config->item('key_amount'), array('class' => 'form-control input-sm')); ?>
					</div>
				</div>
				<div class="form-group form-group-sm col-xs-7">
					<?php echo form_label($this->lang->line('sales_key_payment'), 'key_payment', array('class' => 'control-label col-xs-5')); ?>
					<div class='col-xs-4'>
						<?php echo form_dropdown('key_payment', $key_shortcuts_options, $this->config->item('key_payment'), array('class' => 'form-control input-sm')); ?>
					</div>
				</div>
				<div class="form-group form-group-sm col-xs-7">
					<?php echo form_label($this->lang->line('sales_key_finish_sale'), 'key_complete', array('class' => 'control-label col-xs-5')); ?>
					<div class='col-xs-4'>
						<?php echo form_dropdown('key_complete', $key_shortcuts_options, $this->config->item('key_complete'), array('class' => 'form-control input-sm')); ?>
					</div>
				</div>
				<div class="form-group form-group-sm col-xs-7">
					<?php echo form_label($this->lang->line('sales_key_finish_quote'), 'key_finish', array('class' => 'control-label col-xs-5')); ?>
					<div class='col-xs-4'>
						<?php echo form_dropdown('key_finish', $key_shortcuts_options, $this->config->item('key_finish'), array('class' => 'form-control input-sm')); ?>
					</div>
				</div>
				<div class="form-group form-group-sm col-xs-7">
					<?php echo form_label($this->lang->line('sales_key_help_modal'), 'key_help', array('class' => 'control-label col-xs-5')); ?>
					<div class='col-xs-4'>
						<?php echo form_dropdown('key_help', $key_shortcuts_options, $this->config->item('key_help'), array('class' => 'form-control input-sm')); ?>
					</div>
				</div>
				<?php echo form_submit(array(
					'name' => 'submit_keyshortcuts',
					'id' => 'submit_keyshortcuts',
					'value' => $this->lang->line('common_submit'),
					'class' => 'btn btn-primary btn-sm pull-right'));?>
				<?php echo form_reset(array(
					'name' => 'reset_keyshortcuts',
					'id' => 'reset_keyshortcuts',
					'value' => $this->lang->line('common_reset'),
					'class' => 'btn btn-primary btn-sm pull-right'));?>
		</fieldset>
	</div>
<?php echo form_close(); ?>	
