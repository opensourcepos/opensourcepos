<?php $this->load->view("partial/header"); ?>

<?php
$codes_tab = 'id="codes-tab" data-bs-toggle="tab" href="#codes" role="tab" aria-controls="codes" title="' . $this->lang->line('taxes_tax_codes_configuration') . '"';
$codes_title = '<i class="bi bi-code pe-2"></i>' . $this->lang->line('taxes_tax_codes');

$jurisdictions_tab = 'id="jurisdictions-tab" data-bs-toggle="tab" href="#jurisdictions" role="tab" aria-controls="jurisdictions" title="' . $this->lang->line('taxes_tax_jurisdictions_configuration') . '"';
$jurisdictions_title = '<i class="bi bi-briefcase pe-2"></i>' . $this->lang->line('taxes_tax_jurisdictions');

$categories_tab = 'id="categories-tab" data-bs-toggle="tab" href="#categories" role="tab" aria-controls="categories" title="' . $this->lang->line('taxes_tax_categories_configuration') . '"';
$categories_title = '<i class="bi bi-bookmarks pe-2"></i>' . $this->lang->line('taxes_tax_categories');

$rates_tab = 'id="rates-tab" data-bs-toggle="tab" href="#rates" role="tab" aria-controls="rates" title="' . $this->lang->line('taxes_tax_rate_configuration') . '"';
$rates_title = '<i class="bi bi-percent pe-2"></i>' . $this->lang->line('taxes_tax_rates');
?>

<div class="row">
	<div class="col-lg order-lg-2">
		<div class="list-group d-none d-lg-block sticky-top" id="taxes-list-tab" role="tablist">
			<button class="list-group-item list-group-item-action active" <?= $codes_tab; ?>><?= $codes_title; ?></button>
			<button class="list-group-item list-group-item-action" <?= $jurisdictions_tab; ?>><?= $jurisdictions_title; ?></button>
			<button class="list-group-item list-group-item-action" <?= $categories_tab; ?>><?= $categories_title; ?></button>
			<button class="list-group-item list-group-item-action" <?= $rates_tab; ?>><?= $rates_title; ?></button>
		</div>
		<div class="nav dropdown d-lg-none mb-3">
			<button class="btn btn-primary w-100 dropdown-toggle" id="taxes-dropdown" data-bs-toggle="dropdown" aria-expanded="false">Select Configuration...</button>
			<ul class="dropdown-menu w-100" aria-labbeledby="taxes-dropdown">
				<li><a class="dropdown-item" <?= $codes_tab; ?>><?= $codes_title; ?></a></li>
				<li><a class="dropdown-item" <?= $jurisdictions_tab; ?>><?= $jurisdictions_title; ?></a></li>
				<li><a class="dropdown-item" <?= $categories_tab; ?>><?= $categories_title; ?></a></li>
				<li><a class="dropdown-item" <?= $rates_tab; ?>><?= $rates_title; ?></a></li>
			</ul>
		</div>
	</div>

	<div class="col-lg-9 order-lg-1">
		<div class="tab-content" id="configs-content">
			<div class="tab-pane active" id="codes" role="tabpanel" aria-labelledby="codes-tab"><?php $this->load->view("taxes/tax_codes"); ?></div>
			<div class="tab-pane" id="jurisdictions" role="tabpanel" aria-labelledby="jurisdictions-tab"><?php $this->load->view("taxes/tax_jurisdictions"); ?></div>
			<div class="tab-pane" id="categories" role="tabpanel" aria-labelledby="categories-tab"><?php $this->load->view("taxes/tax_categories"); ?></div>
			<div class="tab-pane" id="rates" role="tabpanel" aria-labelledby="rates-tab"><?php $this->load->view("taxes/tax_rates"); ?></div>
		</div>
	</div>
</div>

<?php $this->load->view("partial/footer"); ?>