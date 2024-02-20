<?php
/**
 * @var bool $logo_exists
 * @var string $controller_name
 * @var array $config
 */
?>
<?= form_open('config/saveInfo/', ['id' => 'info_config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']) ?>
	<div id="config_wrapper">
		<fieldset id="config_info">
			<div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
			<ul id="info_error_message_box" class="error_message_box"></ul>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.company'), 'company', ['class' => 'control-label col-xs-2 required']) ?>
				<div class="col-xs-6">
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-home"></span></span>
						<?= form_input ([
							'name' => 'company',
							'id' => 'company',
							'class' => 'form-control input-sm required',
							'value' => $config['company']
						]) ?>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.company_logo'), 'company_logo', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-6'>
					<div class="fileinput <?= $logo_exists ? 'fileinput-exists' : 'fileinput-new' ?>" data-provides="fileinput">
						<div class="fileinput-new thumbnail" style="width: 200px; height: 200px;"></div>
						<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 200px;">
							<img data-src="holder.js/100%x100%" alt="<?= lang('Config.company_logo') ?>"
								 src="<?php if($logo_exists) echo base_url('uploads/' . $config['company_logo']); else echo '' ?>"
								 style="max-height: 100%; max-width: 100%;">
						</div>
						<div>
							<span class="btn btn-default btn-sm btn-file">
								<span class="fileinput-new"><?= lang('Config.company_select_image') ?></span>
								<span class="fileinput-exists"><?= lang('Config.company_change_image') ?></span>
								<input type="file" name="company_logo">
							</span>
							<a href="#" class="btn btn-default btn-sm fileinput-exists" data-dismiss="fileinput"><?= lang('Config.company_remove_image') ?></a>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.address'), 'address', ['class' => 'control-label col-xs-2 required']) ?>
				<div class='col-xs-6'>
					<?= form_textarea ([
						'name' => 'address',
						'id' => 'address',
						'class' => 'form-control input-sm required',
						'value'=> $config['address']
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.website'), 'website', ['class' => 'control-label col-xs-2']) ?>
				<div class="col-xs-6">
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-globe"></span></span>
						<?= form_input ([
							'name' => 'website',
							'id' => 'website',
							'class' => 'form-control input-sm',
							'value'=> $config['website']
						]) ?>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Common.email'), 'email', ['class' => 'control-label col-xs-2']) ?>
				<div class="col-xs-6">
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-envelope"></span></span>
						<?= form_input ([
							'name' => 'email',
							'id' => 'email',
							'type' => 'email',
							'class' => 'form-control input-sm',
							'value'=> $config['email']
						]) ?>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.phone'), 'phone', ['class' => 'control-label col-xs-2 required']) ?>
				<div class="col-xs-6">
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-phone-alt"></span></span>
						<?= form_input ([
							'name' => 'phone',
							'id' => 'phone',
							'class' => 'form-control input-sm required',
							'value'=> $config['phone']
						]) ?>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.fax'), 'fax', ['class' => 'control-label col-xs-2']) ?>
				<div class="col-xs-6">
					<div class="input-group">
						<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-phone-alt"></span></span>
						<?= form_input ([
							'name' => 'fax',
							'id' => 'fax',
							'class' => 'form-control input-sm',
							'value'=> $config['fax']
						]) ?>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Common.return_policy'), 'return_policy', ['class' => 'control-label col-xs-2 required']) ?>
				<div class='col-xs-6'>
					<?= form_textarea ([
						'name' => 'return_policy',
						'id' => 'return_policy',
						'class' => 'form-control input-sm required',
						'value' => $config['return_policy']
					]) ?>
				</div>
			</div>

			<?= form_submit ([
				'name' => 'submit_info',
				'id' => 'submit_info',
				'value' => lang('Common.submit'),
				'class' => 'btn btn-primary btn-sm pull-right']) ?>
		</fieldset>
	</div>
<?= form_close() ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	$("a.fileinput-exists").click(function() {
		$.ajax({
			type: 'POST',
			url: '<?= "$controller_name/removeLogo"; ?>',
			dataType: 'json'
		})
	});

	$('#info_config_form').validate($.extend(form_support.handler, {

		errorLabelContainer: "#info_error_message_box",

		rules:
		{
			company: "required",
			address: "required",
			phone: "required",
    		email: "email",
    		return_policy: "required"
   		},

		messages:
		{
			company: "<?= lang('Config.company_required') ?>",
			address: "<?= lang('Config.address_required') ?>",
			phone: "<?= lang('Config.phone_required') ?>",
			email: "<?= lang('Common.email_invalid_format') ?>",
			return_policy: "<?= lang('Config.return_policy_required') ?>"
		}
	}));
});
</script>
