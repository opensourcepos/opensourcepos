<?php
$title_license['config_title'] = $this->lang->line('config_license_configuration');
$this->load->view('configs/config_header', $title_license);
?>

<?php foreach ($licenses as $license) { ?>
	<div class="mb-3 mx-3 mx-lg-0">
		<?= form_label($license['title'], 'license', array('class' => 'form-label')); ?>
		<?= form_textarea(array('class' => 'form-control font-monospace', 'style' => 'font-size: .875rem;', 'disabled' => '', 'readonly' => '', 'value' => $license['text'])); ?>
	</div>
<?php } ?>