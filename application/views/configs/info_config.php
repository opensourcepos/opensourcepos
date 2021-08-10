<?= form_open('config/save_info/', array('id' => 'info_config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal')); ?>

<?php
$title_info['config_title'] = $this->lang->line('config_info_configuration');
$this->load->view('configs/config_header', $title_info);
?>

<ul id="info_error_message_box" class="error_message_box"></ul>

<div class="row">
	<div class="col-12 col-lg-6">
		<label for="info-company" class="form-label"><?= $this->lang->line('config_company'); ?><span class="text-warning">*</span></label>
		<div class="input-group mb-3">
			<span class="input-group-text"><i class="bi bi-shop-window"></i></span>
			<input type="text" class="form-control" name="company" id="info-company" value="<?= $this->config->item('company'); ?>" required>
		</div>
	</div>
</div>

<div class="row mb-3">
	<label for="info-company_logo" class="form-label"><?= $this->lang->line('config_company_logo'); ?></label>
	<div class="col-12 col-lg-6">
		<div id="info-company_logo" class="w-100 fileinput <?= $logo_exists ? 'fileinput-exists' : 'fileinput-new'; ?>" data-provides="fileinput">
			<div class="input-group mb-3" aria-describedby="company-logo-desc">
				<span class="input-group-text"><i class="bi bi-image"></i></span>
				<div class="fileinput-new form-control rounded-end mb-0" style="height: 200px;"></div>
				<div class="fileinput-exists fileinput-preview img-thumbnail form-control rounded-end mb-0 bg-light" style="height: 200px; background-size: 40px 40px; background-position: 0 0, 20px 20px; background-image: linear-gradient(45deg, white 25%, transparent 25%, transparent 75%, white 75%, white), linear-gradient(45deg, white 25%, transparent 25%, transparent 75%, white 75%, white);">
					<img class="mx-auto" data-src="holder.js/100%x100%" alt="<?= $this->lang->line('config_company_logo'); ?>" src="<?php if ($logo_exists) echo base_url('uploads/' . $this->config->item('company_logo')); ?>">
				</div>
			</div>
			<div class="btn btn-secondary btn-file me-2">
				<span class="fileinput-new"><i class="bi bi-hand-index pe-1"></i><?= $this->lang->line("config_company_select_image"); ?></span>
				<span class="fileinput-exists"><i class="bi bi-images pe-1"></i><?= $this->lang->line("config_company_change_image"); ?></span>
				<input type="file" name="company_logo">
			</div>
			<button class="btn btn-outline-secondary fileinput-exists" data-dismiss="fileinput"><i class="bi bi-eraser pe-1"></i><?= $this->lang->line("config_company_remove_image"); ?></button>
		</div>
	</div>
	<div class="col-12 col-lg-6 form-text d-none d-lg-block" id="company-logo-desc">
		<ul class="list-unstyled">
			<li>&raquo; Supported file formats; gif, png, jpg</li>
			<li>&raquo; Max upload size of 100kb</li>
			<li>&raquo; Max dimensions of 200x200px</li>
		</ul>
	</div>
</div>

<label for="info-address" class="form-label"><?= $this->lang->line('config_address'); ?><span class="text-warning">*</span></label>
<div class="input-group mb-3">
	<span class="input-group-text"><i class="bi bi-house"></i></span>
	<textarea class="form-control" name="address" id="info-address" rows="10" required><?= $this->config->item('address'); ?></textarea>
</div>

<div class="row">
	<div class="col-12 col-lg-6">
		<label for="info-website" class="form-label"><?= $this->lang->line('config_website'); ?></label>
		<div class="input-group mb-3">
			<span class="input-group-text"><i class="bi bi-globe"></i></span>
			<input type="url" class="form-control" name="website" id="info-website" value="<?= $this->config->item('website'); ?>">
		</div>
	</div>

	<div class="col-12 col-lg-6">
		<label for="info-email" class="form-label"><?= $this->lang->line('config_email'); ?></label>
		<div class="input-group mb-3">
			<span class="input-group-text"><i class="bi bi-at"></i></span>
			<input type="email" class="form-control" name="email" id="info-email" value="<?= $this->config->item('email'); ?>">
		</div>
	</div>

	<div class="col-12 col-lg-6">
		<label for="info-phone" class="form-label"><?= $this->lang->line('config_phone'); ?><span class="text-warning">*</span></label>
		<div class="input-group mb-3">
			<span class="input-group-text"><i class="bi bi-telephone"></i></span>
			<input type="tel" class="form-control" name="phone" id="info-phone" value="<?= $this->config->item('phone'); ?>" required>
		</div>
	</div>

	<div class="col-12 col-lg-6">
		<label for="info-fax" class="form-label"><?= $this->lang->line('config_fax'); ?></label>
		<div class="input-group mb-3">
			<span class="input-group-text"><i class="bi bi-printer"></i></span>
			<input type="tel" class="form-control" name="fax" id="info-fax" value="<?= $this->config->item('fax'); ?>">
		</div>
	</div>
</div>

<label for="info-return_policy" class="form-label"><?= $this->lang->line('common_return_policy'); ?><span class="text-warning">*</span></label>
<div class="input-group mb-3">
	<span class="input-group-text"><i class="bi bi-box-arrow-in-down-left"></i></span>
	<textarea class="form-control" name="return_policy" id="info-return_policy" rows="10" required><?= $this->config->item('return_policy'); ?></textarea>
</div>

<div class="d-flex justify-content-end">
	<button class="btn btn-primary" name="submit_info"><?= $this->lang->line('common_submit'); ?></button>
</div>

<?= form_close(); ?>

<script type="text/javascript">
	//validation and submit handling
	document.querySelector(document).ready(function() {
		document.querySelector("button.fileinput-exists").click(function() {
			$.ajax({
				type: "GET",
				url: '<?= site_url("$controller_name/remove_logo"); ?>',
				dataType: "json",
			});
		});

		document.querySelector("#info_config_form").validate(
			$.extend(form_support.handler, {
				errorLabelContainer: "#info_error_message_box",

				rules: {
					company: "required",
					address: "required",
					phone: "required",
					email: "email",
					return_policy: "required",
				},

				messages: {
					company: "<?= $this->lang->line('config_company_required'); ?>",
					address: "<?= $this->lang->line('config_address_required'); ?>",
					phone: "<?= $this->lang->line('config_phone_required'); ?>",
					email: "<?= $this->lang->line('common_email_invalid_format'); ?>",
					return_policy: "<?= $this->lang->line('config_return_policy_required'); ?>",
				},
			})
		);
	});
</script>