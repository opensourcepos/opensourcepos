<?php
/**
 * @var array $allowed_modules
 */
?>

<?= view('partial/header_adminlte') ?>

<script type="text/javascript">
    dialog_support.init("a.modal-dlg");
</script>

<!-- Page Header -->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0"><i class="fas fa-home me-2"></i><?= lang('Common.welcome_message') ?></h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item active">Home</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Module Grid -->
<div class="row">
    <?php
    $module_icons = [
        'home' => 'fa-home',
        'office' => 'fa-building',
        'sales' => 'fa-shopping-cart',
        'receivings' => 'fa-truck',
        'items' => 'fa-boxes',
        'item_kits' => 'fa-box-open',
        'suppliers' => 'fa-industry',
        'customers' => 'fa-users',
        'employees' => 'fa-user-tie',
        'giftcards' => 'fa-gift',
        'reports' => 'fa-chart-bar',
        'config' => 'fa-cog',
        'expenses' => 'fa-file-invoice-dollar',
        'expenses_categories' => 'fa-tags',
        'taxes' => 'fa-percent',
        'cashups' => 'fa-cash-register',
        'attributes' => 'fa-star',
        'messages' => 'fa-envelope',
    ];

    $module_colors = [
        'sales' => 'bg-primary',
        'receivings' => 'bg-success',
        'items' => 'bg-info',
        'customers' => 'bg-warning',
        'reports' => 'bg-danger',
        'config' => 'bg-secondary',
    ];

    foreach ($allowed_modules as $module):
        $icon = $module_icons[$module->module_id] ?? 'fa-circle';
        $color = $module_colors[$module->module_id] ?? 'bg-primary';
        ?>
        <div class="col-lg-3 col-md-4 col-sm-6 col-12">
            <a href="<?= base_url($module->module_id) ?>" class="text-decoration-none">
                <div class="small-box <?= $color ?> mb-4">
                    <div class="inner">
                        <h4><?= lang("Module.$module->module_id") ?></h4>
                        <p><?= lang("Module.$module->module_id" . '_desc') ?></p>
                    </div>
                    <div class="icon">
                        <i class="fas <?= $icon ?>"></i>
                    </div>
                    <span class="small-box-footer">
                        <?= lang('Common.go') ?> <i class="fas fa-arrow-circle-right"></i>
                    </span>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<?= view('partial/footer_adminlte') ?>