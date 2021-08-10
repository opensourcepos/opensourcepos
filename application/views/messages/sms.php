<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>

<?= form_open("messages/send/", array('id' => 'send_sms_form', 'enctype' => 'multipart/form-data', 'method' => 'post', 'class' => 'form-horizontal')); ?>
<h5><?= $this->lang->line('messages_sms_send'); ?></h5>
<div class="col mb-3">
	<label for="message-recipients" class="form-label"><?= $this->lang->line('messages_phone'); ?></label>
	<div class="input-group">
		<span class="input-group-text" id="message-icon"><i class="bi bi-phone"></i></span>
		<input type="text" name="phone" class="form-control" id="message-recipients" aria-describedby="message-icon" required placeholder="<?= $this->lang->line('messages_phone_placeholder'); ?>">
	</div>
	<span class="form-text"><?= $this->lang->line('messages_multiple_phones'); ?></span>
</div>

<div class="col mb-3">
	<label for="text-message" class="form-label"><?= $this->lang->line('messages_message'); ?></label>
	<div class="input-group">
		<span class="input-group-text"><i class="bi bi-chat-quote"></i></span>
		<textarea class="form-control" name="message" id="text-message" rows="10" placeholder="<?= $this->lang->line('messages_message_placeholder'); ?>"></textarea>
	</div>
</div>

<div class="d-flex justify-content-end">
	<button class="btn btn-primary" id="submit_form" name="submit_form"><?= $this->lang->line('common_submit'); ?></button>
</div>
<?= form_close(); ?>

<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript">
	//validation and submit handling
	$(document).ready(function() {
		$('#send_sms_form').validate({
			submitHandler: function(form) {
				$(form).ajaxSubmit({
					success: function(response) {
						$.notify({
							message: response.message
						}, {
							type: response.success ? 'success' : 'danger'
						})
					},
					dataType: 'json'
				});
			}
		});
	});
</script>