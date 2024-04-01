<?php
/**
 * @var array $allowed_modules
 */
?>
<?= view('partial/header') ?>

<script type="application/javascript">
	dialog_support.init("a.modal-dlg");
</script>

<h3 class="text-center"><?= lang('Common.welcome_message') ?></h3>

<div id="office_module_list">
	<?php
	foreach($allowed_modules as $module)
	{
	?>
		<div class="module_item" title="<?= lang("Module.$module->module_id" . '_desc') ?>">
			<a href="<?= base_url($module->module_id) ?>"><img src="<?= base_url("images/menubar/$module->module_id.png") ?>" style="border-width: 0;" alt="Menubar Image" /></a>
			<a href="<?= base_url($module->module_id) ?>"><?= lang("Module.$module->module_id") ?></a>
		</div>
	<?php
	}
	?>
</div>

<?= view('partial/footer') ?>
