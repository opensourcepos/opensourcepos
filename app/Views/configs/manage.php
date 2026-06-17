<?php
$tabs = [
    ['id' => 'info',         'icon' => 'bi-shop',                'label' => lang('Config.info'),         'title' => lang('Config.info_configuration')],
    ['id' => 'general',      'icon' => 'bi-sliders',             'label' => lang('Config.general'),      'title' => lang('Config.general_configuration')],
    ['id' => 'appearance',   'icon' => 'bi-eye',                 'label' => 'Appearance',                'title' => 'Appearance Configuration'],
    ['id' => 'locale',       'icon' => 'bi-translate',           'label' => lang('Config.locale'),       'title' => lang('Config.locale_configuration')],
    ['id' => 'tax',          'icon' => 'bi-piggy-bank',          'label' => lang('Config.tax'),          'title' => lang('Config.tax_configuration')],
    ['id' => 'barcode',      'icon' => 'bi-upc-scan',            'label' => lang('Config.barcode'),      'title' => lang('Config.barcode_configuration')],
    ['id' => 'stock',        'icon' => 'bi-truck',               'label' => lang('Config.location'),     'title' => lang('Config.location_configuration')],
    ['id' => 'receipt',      'icon' => 'bi-receipt',             'label' => lang('Config.receipt'),      'title' => lang('Config.receipt_configuration')],
    ['id' => 'invoice',      'icon' => 'bi-file-text',           'label' => lang('Config.invoice'),      'title' => lang('Config.invoice_configuration')],
    ['id' => 'shortcuts',    'icon' => 'bi-shift',               'label' => lang('Config.shortcuts'),    'title' => lang('Config.shortcuts_configuration')],
    ['id' => 'reward',       'icon' => 'bi-trophy',              'label' => lang('Config.reward'),       'title' => lang('Config.reward_configuration')],
    ['id' => 'table',        'icon' => 'bi-cup-straw',           'label' => lang('Config.table'),        'title' => lang('Config.table_configuration')],
    ['id' => 'email',        'icon' => 'bi-envelope',            'label' => lang('Config.email'),        'title' => lang('Config.email_configuration')],
    ['id' => 'message',      'icon' => 'bi-chat',                'label' => lang('Config.message'),      'title' => lang('Config.message_configuration')],
    ['id' => 'integrations', 'icon' => 'bi-gear-wide-connected', 'label' => lang('Config.integrations'), 'title' => lang('Config.integrations_configuration')],
    ['id' => 'system',       'icon' => 'bi-info-circle',         'label' => lang('Config.system_info'),  'title' => lang('Config.system_info'), 'view' => 'configs/system_info'],
    ['id' => 'license',      'icon' => 'bi-journal-check',       'label' => lang('Config.license'),      'title' => lang('Config.license_configuration')],
];
?>
<?= view('partial/header') ?>

<script type="text/javascript" src="resources/clipboard/clipboard.min.js"></script>

<div class="row">
    <div class="col-lg-3 <?= $config['config_menu_position'] == 'start' ? '' : 'order-lg-2' ?>">
        <div class="list-group d-none d-lg-block" role="tablist">
            <?php foreach ($tabs as $i => $tab): ?>
                <button type="button" class="list-group-item list-group-item-action text-truncate <?= $i === 0 ? 'active' : '' ?>" id="<?= $tab['id'] ?>-tab" data-bs-toggle="tab" data-bs-target="#<?= $tab['id'] ?>" role="tab" title="<?= $tab['title'] ?>">
                    <i class="bi <?= $tab['icon'] ?> me-2"></i><?= $tab['label'] ?>
                </button>
            <?php endforeach ?>
        </div>

        <div class="nav dropdown d-lg-none mb-3">
            <button type="button" class="btn btn-primary w-100 dropdown-toggle text-truncate" id="configs-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <?= lang('Config.info') ?>
            </button>
            <ul class="dropdown-menu w-100" aria-labelledby="configs-dropdown">
                <?php foreach ($tabs as $i => $tab): ?>
                    <li>
                        <a class="dropdown-item py-2 text-truncate <?= $i === 0 ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#<?= $tab['id'] ?>" role="tab" title="<?= $tab['title'] ?>">
                            <i class="bi <?= $tab['icon'] ?> me-2"></i><?= $tab['label'] ?>
                        </a>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
    </div>

    <div class="col-lg-9 order-lg-1">
        <div class="tab-content">
            <?php foreach ($tabs as $i => $tab): ?>
                <div class="tab-pane <?= $i === 0 ? 'active' : '' ?>" id="<?= $tab['id'] ?>" role="tabpanel" aria-labelledby="<?= $tab['id'] ?>-tab">
                    <?= view($tab['view'] ?? 'configs/' . $tab['id'] . '_config') ?>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>

<script type="text/javascript" src="js/bs-tab_anchor_linking.js"></script>
<script type="text/javascript" src="js/bs-validation.js"></script>

<?= view('partial/footer') ?>
