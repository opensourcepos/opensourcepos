<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>
	
<?php echo form_open("messages/send_form/".$person_info->person_id, array('id'=>'send_sms_form', 'class'=>'form-horizontal')); ?>
	<fieldset>
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('messages_first_name'), 'first_name_label', array('for'=>'first_name', 'class'=>'control-label col-xs-2')); ?>
			<div class="col-xs-10">
				<?php echo form_input(array('class'=>'form-control input-sm', 'type'=>'text', 'name'=>'first_name', 'value'=>$person_info->first_name, 'readonly'=>'true'));?>
			</div>
		</div>
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('messages_last_name'), 'last_name_label', array('for'=>'last_name', 'class'=>'control-label col-xs-2')); ?>
			<div class="col-xs-10">
				<?php echo form_input(array('class'=>'form-control input-sm', 'type'=>'text', 'name'=>'last_name', 'value'=>$person_info->last_name, 'readonly'=>'true'));?>
			</div>
		</div> 
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('messages_phone'), 'phone_label', array('for'=>'phone', 'class'=>'control-label col-xs-2 required')); ?>
			<div class="col-xs-10">
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-phone-alt"></span></span>
					<?php echo form_input(array('class'=>'form-control input-sm required', 'type'=>'text', 'name'=>'phone', 'value'=>$person_info->phone_number));?>
				</div>
			</div>
		</div>
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('messages_message'), 'message_label', array('for'=>'message', 'class'=>'control-label col-xs-2 required')); ?>
			<div class="col-xs-10">
				<?php echo form_textarea(array('class'=>'form-control input-sm required', 'name'=>'message', 'id'=>'message', 'value'=>$this->config->item('msg_msg')));?>
			</div>
		</div>
	</fieldset>
<?php echo form_close(); ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	$('#send_sms_form').validate($.extend({
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				success: function(response)
				{
					dialog_support.hide();
					table_support.handle_submit("<?php echo site_url($controller_name); ?>", response);
				},
				dataType: 'json'
			});
		},

		errorLabelContainer: '#error_message_box',

		rules:
		{
			phone:
			{
				required: true,
				number: true
			},
			message:
			{
				required: true,
				number: false
			}
   		},

		messages:
		{
			phone:
			{
				required: "<?php echo $this->lang->line('messages_phone_number_required'); ?>",
				number: "<?php echo $this->lang->line('messages_phone'); ?>"
			},
			message:
			{
				required: "<?php echo $this->lang->line('messages_message_required'); ?>"
			}
		}
	}, form_support.error));
});
</script>
