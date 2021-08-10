<?php $this->load->view("partial/header"); ?>

<h3 class="text-center pb-3"><?= $this->lang->line('common_welcome_message'); ?></h3>

<section class="container-fluid d-flex flex-wrap justify-content-center gap-3 p-0">
	<?php foreach ($allowed_modules as $module) { ?>
		<div class="border border-primary rounded shadow-sm bg-light text-center d-block" title="<?= $this->lang->line('module_' . $module->module_id . '_desc'); ?>">
			<a href="<?= site_url("$module->module_id"); ?>">
				<img class="d-block mx-auto p-2" src="<?= base_url() . 'images/menubar/' . $module->module_id . '.svg'; ?>" alt="Menubar Image" />
			</a>
			<a href="<?= site_url("$module->module_id"); ?>">
				<div class="tile-text rounded-bottom d-block bg-primary text-light fw-bold p-2">
					<span><?= $this->lang->line("module_" . $module->module_id) ?></span>
				</div>
			</a>
		</div>
	<?php } ?>
</section>

<?php $this->load->view("partial/footer"); ?>