<?= view('partial/header') ?>

<!-- Scripts only used in Configuration screen -->
<script type="text/javascript" src="resources/clipboard/clipboard.min.js"></script>

<div class="row">
    <div class="col-lg-3 <?= $config['config_menu_position'] == 'start' ? '' : 'order-lg-2' ?>">
        <div class="list-group d-none d-lg-block" role="tablist">
            <button class="list-group-item list-group-item-action text-truncate active" id="info-tab" data-bs-toggle="tab" href="#info" title="<?= lang('Config.info_configuration'); ?>" type="button" role="tab">
                <i class="bi bi-shop me-2"></i><?= lang('Config.info'); ?>
            </button>
            <button type="button" class="list-group-item list-group-item-action text-truncate" id="general-tab" data-bs-toggle="tab" href="#general" title="<?= lang('Config.general_configuration'); ?>" type="button" role="tab">
                <i class="bi bi-sliders me-2"></i><?= lang('Config.general'); ?>
            </button>
            <button type="button" class="list-group-item list-group-item-action text-truncate" id="appearance-tab" data-bs-toggle="tab" href="#appearance" title="Appearance Configuration" type="button" role="tab">
                <i class="bi bi-eye me-2"></i>Appearance
            </button>
            <button type="button" class="list-group-item list-group-item-action text-truncate" id="locale-tab" data-bs-toggle="tab" href="#locale" title="<?= lang('Config.locale_configuration'); ?>" type="button" role="tab">
                <i class="bi bi-translate me-2"></i><?= lang('Config.locale'); ?>
            </button>
            <button type="button" class="list-group-item list-group-item-action text-truncate" id="tax-tab" data-bs-toggle="tab" href="#tax" title="<?= lang('Config.tax_configuration'); ?>" type="button" role="tab">
                <i class="bi bi-piggy-bank me-2"></i><?= lang('Config.tax'); ?>
            </button>
            <button type="button" class="list-group-item list-group-item-action text-truncate" id="barcode-tab" data-bs-toggle="tab" href="#barcode" title="<?= lang('Config.barcode_configuration'); ?>" type="button" role="tab">
                <i class="bi bi-upc-scan me-2"></i><?= lang('Config.barcode'); ?>
            </button>
            <button type="button" class="list-group-item list-group-item-action text-truncate" id="stock-tab" data-bs-toggle="tab" href="#stock" title="<?= lang('Config.location_configuration'); ?>" type="button" role="tab">
                <i class="bi bi-truck me-2"></i><?= lang('Config.location'); ?>
            </button>
            <button type="button" class="list-group-item list-group-item-action text-truncate" id="receipt-tab" data-bs-toggle="tab" href="#receipt" title="<?= lang('Config.receipt_configuration'); ?>" type="button" role="tab">
                <i class="bi bi-receipt me-2"></i><?= lang('Config.receipt'); ?>
            </button>
            <button type="button" class="list-group-item list-group-item-action text-truncate" id="invoice-tab" data-bs-toggle="tab" href="#invoice" title="<?= lang('Config.invoice_configuration'); ?>" type="button" role="tab">
                <i class="bi bi-file-text me-2"></i><?= lang('Config.invoice'); ?>
            </button>
            <button type="button" class="list-group-item list-group-item-action text-truncate" id="reward-tab" data-bs-toggle="tab" href="#reward" title="<?= lang('Config.reward_configuration'); ?>" type="button" role="tab">
                <i class="bi bi-trophy me-2"></i><?= lang('Config.reward'); ?>
            </button>
            <button type="button" class="list-group-item list-group-item-action text-truncate" id="table-tab" data-bs-toggle="tab" href="#table" title="<?= lang('Config.table_configuration'); ?>" type="button" role="tab">
                <i class="bi bi-cup-straw me-2"></i><?= lang('Config.table'); ?>
            </button>
            <button type="button" class="list-group-item list-group-item-action text-truncate" id="email-tab" data-bs-toggle="tab" href="#email" title="<?= lang('Config.email_configuration'); ?>" type="button" role="tab">
                <i class="bi bi-envelope me-2"></i><?= lang('Config.email'); ?>
            </button>
            <button type="button" class="list-group-item list-group-item-action text-truncate" id="message-tab" data-bs-toggle="tab" href="#message" title="<?= lang('Config.message_configuration'); ?>" type="button" role="tab">
                <i class="bi bi-chat me-2"></i><?= lang('Config.message'); ?>
            </button>
            <button type="button" class="list-group-item list-group-item-action text-truncate" id="integrations-tab" data-bs-toggle="tab" href="#integrations" title="<?= lang('Config.integrations_configuration'); ?>" type="button" role="tab">
                <i class="bi bi-gear-wide-connected me-2"></i><?= lang('Config.integrations'); ?>
            </button>
            <button type="button" class="list-group-item list-group-item-action text-truncate" id="system-tab" data-bs-toggle="tab" href="#system" title="<?= lang('Config.system_info'); ?>" type="button" role="tab">
                <i class="bi bi-info-circle me-2"></i><?= lang('Config.system_info'); ?>
            </button>
            <button type="button" class="list-group-item list-group-item-action text-truncate" id="license-tab" data-bs-toggle="tab" href="#license" title="<?= lang('Config.license_configuration'); ?>" type="button" role="tab">
                <i class="bi bi-journal-check me-2"></i><?= lang('Config.license'); ?>
            </button>
        </div>

        <div class="nav dropdown d-lg-none mb-3">
            <button class="btn btn-primary w-100 dropdown-toggle text-truncate" id="configs-dropdown" data-bs-toggle="dropdown" aria-expanded="false">Select Configuration...</button> <!-- TODO-BS5 translate -->
            <ul class="dropdown-menu w-100" aria-labelledby="configs-dropdown">
                <li>
                    <a class="dropdown-item py-2 text-truncate active" id="info-tab" data-bs-toggle="tab" href="#info" role="tab" title="<?= lang('Config.info_configuration'); ?>">
                        <i class="bi bi-shop me-2"></i><?= lang('Config.info'); ?>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 text-truncate" id="general-tab" data-bs-toggle="tab" href="#general" role="tab" title="<?= lang('Config.general_configuration'); ?>">
                        <i class="bi bi-sliders me-2"></i><?= lang('Config.general'); ?>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 text-truncate" id="appearance-tab" data-bs-toggle="tab" href="#appearance" role="tab" title="Appearance Configuration">
                        <i class="bi bi-eye me-2"></i>Appearance
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 text-truncate" id="locale-tab" data-bs-toggle="tab" href="#locale" role="tab" title="<?= lang('Config.locale_configuration'); ?>">
                        <i class="bi bi-translate me-2"></i><?= lang('Config.locale'); ?>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 text-truncate" id="tax-tab" data-bs-toggle="tab" href="#tax" role="tab" title="<?= lang('Config.tax_configuration'); ?>">
                        <i class="bi bi-piggy-bank me-2"></i><?= lang('Config.tax'); ?>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 text-truncate" id="barcode-tab" data-bs-toggle="tab" href="#barcode" role="tab" title="<?= lang('Config.barcode_configuration'); ?>">
                        <i class="bi bi-upc-scan me-2"></i><?= lang('Config.barcode'); ?>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 text-truncate" id="stock-tab" data-bs-toggle="tab" href="#stock" role="tab" title="<?= lang('Config.location_configuration'); ?>">
                        <i class="bi bi-truck me-2"></i><?= lang('Config.location'); ?>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 text-truncate" id="receipt-tab" data-bs-toggle="tab" href="#receipt" role="tab" title="<?= lang('Config.receipt_configuration'); ?>">
                        <i class="bi bi-receipt me-2"></i><?= lang('Config.receipt'); ?>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 text-truncate" id="invoice-tab" data-bs-toggle="tab" href="#invoice" role="tab" title="<?= lang('Config.invoice_configuration'); ?>">
                        <i class="bi bi-file-text me-2"></i><?= lang('Config.invoice'); ?>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 text-truncate" id="reward-tab" data-bs-toggle="tab" href="#reward" role="tab" title="<?= lang('Config.reward_configuration'); ?>">
                        <i class="bi bi-trophy me-2"></i><?= lang('Config.reward'); ?>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 text-truncate" id="table-tab" data-bs-toggle="tab" href="#table" role="tab" title="<?= lang('Config.table_configuration'); ?>">
                        <i class="bi bi-cup-straw me-2"></i><?= lang('Config.table'); ?>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 text-truncate" id="email-tab" data-bs-toggle="tab" href="#email" role="tab" title="<?= lang('Config.email_configuration'); ?>">
                        <i class="bi bi-envelope me-2"></i><?= lang('Config.email'); ?>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 text-truncate" id="message-tab" data-bs-toggle="tab" href="#message" role="tab" title="<?= lang('Config.message_configuration'); ?>">
                        <i class="bi bi-chat me-2"></i><?= lang('Config.message'); ?>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 text-truncate" id="integrations-tab" data-bs-toggle="tab" href="#integrations" role="tab" title="<?= lang('Config.integrations_configuration'); ?>">
                        <i class="bi bi-gear-wide-connected me-2"></i><?= lang('Config.integrations'); ?>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 text-truncate" id="system-tab" data-bs-toggle="tab" href="#system" role="tab" title="<?= lang('Config.system_info'); ?>">
                        <i class="bi bi-info-circle me-2"></i><?= lang('Config.system_info'); ?>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 text-truncate" id="license-tab" data-bs-toggle="tab" href="#license" role="tab" title="<?= lang('Config.license_configuration'); ?>">
                        <i class="bi bi-journal-check me-2"></i><?= lang('Config.license'); ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="col-lg-9 order-lg-1">
        <div class="tab-content">
            <div class="tab-pane active" id="info" role="tabpanel" aria-labelledby="info-tab">
                <?= view('configs/info_config') ?>
            </div>
            <div class="tab-pane" id="general" role="tabpanel" aria-labelledby="general-tab">
                <?= view('configs/general_config') ?>
            </div>
            <div class="tab-pane" id="appearance" role="tabpanel" aria-labelledby="appearance-tab">
                <?= view('configs/appearance_config') ?>
            </div>
            <div class="tab-pane" id="locale" role="tabpanel" aria-labelledby="locale-tab">
                <?= view('configs/locale_config') ?>
            </div>
            <div class="tab-pane" id="tax" role="tabpanel" aria-labelledby="tax-tab">
                <?= view('configs/tax_config') ?>
            </div>
            <div class="tab-pane" id="barcode" role="tabpanel" aria-labelledby="barcode-tab">
                <?= view('configs/barcode_config') ?>
            </div>
            <div class="tab-pane" id="stock" role="tabpanel" aria-labelledby="stock-tab">
                <?= view('configs/stock_config') ?>
            </div>
            <div class="tab-pane" id="receipt" role="tabpanel" aria-labelledby="receipt-tab">
                <?= view('configs/receipt_config') ?>
            </div>
            <div class="tab-pane" id="invoice" role="tabpanel" aria-labelledby="invoice-tab">
                <?= view('configs/invoice_config') ?>
            </div>
            <div class="tab-pane" id="reward" role="tabpanel" aria-labelledby="reward-tab">
                <?= view('configs/reward_config') ?>
            </div>
            <div class="tab-pane" id="table" role="tabpanel" aria-labelledby="table-tab">
                <?= view('configs/table_config') ?>
            </div>
            <div class="tab-pane" id="email" role="tabpanel" aria-labelledby="email-tab">
                <?= view('configs/email_config') ?>
            </div>
            <div class="tab-pane" id="message" role="tabpanel" aria-labelledby="message-tab">
                <?= view('configs/message_config') ?>
            </div>
            <div class="tab-pane" id="integrations" role="tabpanel" aria-labelledby="integrations-tab">
                <?= view('configs/integrations_config') ?>
            </div>
            <div class="tab-pane" id="system" role="tabpanel" aria-labelledby="system-tab">
                <?= view('configs/system_info') ?>
            </div>
            <div class="tab-pane" id="license" role="tabpanel" aria-labelledby="license-tab">
                <?= view('configs/license_config') ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="js/bs-tab_anchor_linking.js"></script>
<script type="text/javascript" src="js/bs-validation.js"></script>

<?= view('partial/footer') ?>
