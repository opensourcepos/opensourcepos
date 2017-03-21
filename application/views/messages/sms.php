<?php $this->load->view("partial/header"); ?>
	      
<div class="jumbotron" style="max-width: 60%; margin:auto">
	<?php echo form_open("messages/send/", array('id'=>'send_sms_form', 'enctype'=>'multipart/form-data', 'method'=>'post', 'class'=>'form-horizontal')); ?>
		<fieldset>
			<legend style="text-align: center;"><?php echo $this->lang->line('messages_sms_send'); ?></legend>
			<div class="form-group form-group-sm">
				<label for="phone" class="col-xs-3 control-label"><?php echo $this->lang->line('messages_phone'); ?></label>
				<div class="col-xs-9">
					<input class="form-control input-sm", type="text", name="phone", placeholder="<?php echo $this->lang->line('messages_phone_placeholder'); ?>"></input>
					<span class="help-block" style="text-align:center;"><?php echo $this->lang->line('messages_multiple_phones'); ?></span>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<label for="message" class="col-xs-3 control-label"><?php echo $this->lang->line('messages_message'); ?></label>
				<div class="col-xs-9">
					<textarea class="form-control input-sm" rows="3" id="message" name="message" placeholder="<?php echo $this->lang->line('messages_message_placeholder'); ?>"></textarea>
				</div>
			</div>

			<?php echo form_submit(array(
				'name'=>'submit_form',
				'id'=>'submit_form',
				'value'=>$this->lang->line('common_submit'),
				'class'=>'btn btn-primary btn-sm pull-right'));?>
		</fieldset>
	<?php echo form_close(); ?>
</div>

<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	$('#send_sms_form').validate({
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				success: function(response)	{
					$.notify(response.message, { type: response.success ? 'success' : 'danger'} );
				},
				dataType: 'json'
			});
		}
	});
});
</script>
