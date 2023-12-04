<?= view('partial/header') ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>
	      
<div class="jumbotron" style="max-width: 60%; margin:auto">
	<?= form_open("messages/send/", ['id' => 'send_sms_form', 'enctype' => 'multipart/form-data', 'method' => 'post', 'class' => 'form-horizontal']) ?>
		<fieldset>
			<legend style="text-align: center;"><?= lang('Messages.sms_send') ?></legend>
			<div class="form-group form-group-sm">
				<label for="phone" class="col-xs-3 control-label"><?= lang('Messages.phone') ?></label>
				<div class="col-xs-9">
					<input class="form-control input-sm" type="text" name="phone" placeholder="<?= lang('Messages.phone_placeholder') ?>" />
					<span class="help-block" style="text-align:center;"><?= lang('Messages.multiple_phones') ?></span>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<label for="message" class="col-xs-3 control-label"><?= lang('Messages.message') ?></label>
				<div class="col-xs-9">
					<textarea class="form-control input-sm" rows="3" id="message" name="message" placeholder="<?= lang('Messages.message_placeholder') ?>"></textarea>
				</div>
			</div>

			<?= form_submit ([
				'name' => 'submit_form',
				'id' => 'submit_form',
				'value' => lang('Common.submit'),
				'class' => 'btn btn-primary btn-sm pull-right']) ?>
		</fieldset>
	<?= form_close() ?>
</div>

<?= view('partial/footer') ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	$('#send_sms_form').validate({
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				success: function(response)	{
					$.notify( { message: response.message }, { type: response.success ? 'success' : 'danger'} )
				},
				dataType: 'json'
			});
		}
	});
});
</script>
