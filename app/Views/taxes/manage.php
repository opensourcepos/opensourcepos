<?php
/**
 * @var string $controller_name
 */
?>

<?= view('partial/header') ?>

<?php
$title_info['config_title'] = 'Taxes';
echo view('configs/config_header', $title_info);
?>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="codes-tab" data-bs-toggle="tab" data-bs-target="#tax_codes_tab" type="button" role="tab" aria-controls="tax_codes_tab" title="<?= lang(ucfirst($controller_name) . '.tax_codes_configuration') ?>">
            <?= lang(ucfirst($controller_name) . '.tax_codes') ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="jurisdictions-tab" data-bs-toggle="tab" data-bs-target="#tax_jurisdictions_tab" type="button" role="tab" aria-controls="tax_jurisdictions_tab" title="<?= lang(ucfirst($controller_name) . '.tax_jurisdictions_configuration') ?>">
            <?= lang(ucfirst($controller_name) . '.tax_jurisdictions') ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#tax_categories_tab" type="button" role="tab" aria-controls="tax_categories_tab" title="<?= lang(ucfirst($controller_name) . '.tax_categories_configuration') ?>">
            <?= lang(ucfirst($controller_name) . '.tax_categories') ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="rates-tab" data-bs-toggle="tab" data-bs-target="#tax_rates_tab" type="button" role="tab" aria-controls="tax_rates_tab" title="<?= lang(ucfirst($controller_name) . '.tax_rate_configuration') ?>">
            <?= lang(ucfirst($controller_name) . '.tax_rates') ?>
        </button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane active" id="tax_codes_tab" role="tabpanel" aria-labelledby="codes-tab" tabindex="0">
        <?= view('taxes/tax_codes') ?>
    </div>
    <div class="tab-pane" id="tax_jurisdictions_tab" role="tabpanel" aria-labelledby="jurisdictions-tab" tabindex="0">
        <?= view('taxes/tax_jurisdictions') ?>
    </div>
    <div class="tab-pane" id="tax_categories_tab" role="tabpanel" aria-labelledby="categories-tab" tabindex="0">
        <?= view('taxes/tax_categories') ?>
    </div>
    <div class="tab-pane" id="tax_rates_tab" role="tabpanel" aria-labelledby="rates-tab" tabindex="0">
        <?= view('taxes/tax_rates') ?>
    </div>
</div>

<?= view('partial/footer') ?>
