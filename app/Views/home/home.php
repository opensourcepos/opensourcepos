<?php
/**
 * @var array $allowed_modules
 */
?>
<?php echo view('partial/header') ?>

<script type="text/javascript">
	dialog_support.init("a.modal-dlg");
</script>

<h3 class="text-center"><?php echo lang('Common.welcome_message') ?></h3>

<div id="home_module_list">
	<?php
	foreach($allowed_modules as $module)
	{
	?>
		<div class="module_item" title="<?php echo lang("Module.$module->module_id" . '_desc') ?>">
			<a href="<?php echo esc(site_url($module->module_id), 'url') ?>"><img src="<?php echo esc(base_url() . "images/menubar/$module->module_id.png", 'url') ?>" style="border-width: 0;" alt="Menubar Image" /></a>
			<a href="<?php echo esc(site_url($module->module_id), 'url') ?>"><?php echo lang("Module.$module->module_id") ?></a>
		</div>
	<?php
	}
	?>
</div>

<?php echo view('partial/footer') ?>
