<?php echo form_open('config/save_mailchimp/', array('id' => 'mailchimp_config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal')); ?>
	<div id="config_wrapper">
		<fieldset id="config_info">
			<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
			<div id="integrations_header"><?php echo $this->lang->line('config_mailchimp_configuration')?></div>
			<ul id="mailchimp_error_message_box" class="error_message_box"></ul>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_mailchimp_api_key'), 'mailchimp_api_key', array('class' => 'control-label col-xs-2')); ?>
				<div class="col-xs-4">
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-cloud"></span></span>
						<?php echo form_input(array(
							'name' => 'mailchimp_api_key',
							'id' => 'mailchimp_api_key',
							'class' => 'form-control input-sm',
							'value' => $mailchimp['api_key'])); ?>
					</div>
				</div>
				<div class="col-xs-1">
					<label class="control-label">
						<a href="http://eepurl.com/b9a05b" target="_blank"><span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="right" title="<?php echo $this->lang->line('config_mailchimp_tooltip'); ?>"></span></a>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_mailchimp_lists'), 'mailchimp_list_id', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-4'>
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-user"></span></span>
						<?php echo form_dropdown('mailchimp_list_id',
							$mailchimp['lists'],
							$mailchimp['list_id'],
							array('id' => 'mailchimp_list_id', 'class' => 'form-control input-sm')); ?>
					</div>
				</div>
			</div>

			<?php echo form_submit(array(
				'name' => 'submit_mailchimp',
				'id' => 'submit_mailchimp',
				'value' => $this->lang->line('common_submit'),
				'class' => 'btn btn-primary btn-sm pull-right')); ?>
		</fieldset>
	</div>
<?php echo form_close(); ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	$('#mailchimp_api_key').change(function() {
		$.post("<?php echo site_url($controller_name . '/ajax_check_mailchimp_api_key')?>", {
				'mailchimp_api_key': $('#mailchimp_api_key').val()
			},
			function(response) {
				$.notify({message: response.message}, {type: response.success ? 'success' : 'danger'} );
				$('#mailchimp_list_id').empty();
				$.each(response.mailchimp_lists, function(val, text) {
					$('#mailchimp_list_id').append(new Option(text, val));
				});
				$('#mailchimp_list_id').prop('selectedIndex', 0);
			},
			'json'
		);
	});

	$('#mailchimp_config_form').validate($.extend(form_support.handler, {
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				success: function(response) {
					$.notify( { message: response.message }, { type: response.success ? 'success' : 'danger'} )
				},
				dataType: 'json'
			});
		},

		errorLabelContainer: '#mailchimp_error_message_box'
	}));
});
</script>
