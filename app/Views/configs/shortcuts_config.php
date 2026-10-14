<?php
/**
 * @var array $config
 * @var array $keyboardShortcutOptions
 * @var array $keyboardShortcuts
 */

$keyboardShortcuts ??= [];
$keyboardShortcutOptions ??= [];
$config ??= [];

$shortcutLabels = [
    'cancel'    => lang('Sales.key_cancel'),
    'items'     => lang('Sales.key_item_search'),
    'customers' => lang('Sales.key_customer_search'),
    'suspend'   => lang('Sales.key_suspend'),
    'suspended' => lang('Sales.key_suspended'),
    'amount'    => lang('Sales.key_tendered'),
    'payment'   => lang('Sales.key_payment'),
    'complete'  => lang('Sales.key_finish_sale'),
    'finish'    => lang('Sales.key_finish_quote'),
    'help'      => lang('Sales.key_help_modal')
];
?>

<?= form_open('config/saveShortcuts', ['id' => 'shortcuts_config_form']) ?>

    <?php
    $title_info['config_title'] = lang('Config.shortcuts_configuration');
    echo view('configs/config_header', $title_info);
    ?>

    <ul id="error_message_box" class="shortcuts_error_message_box alert alert-warning d-none"></ul>

    <div class="row">
        <?php foreach ($shortcutLabels as $name => $label): ?>
            <div class="col-12 col-lg-6">
                <?= form_label($label, 'key_' . $name, ['class' => 'form-label']) ?>
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-keyboard"></i></span>
                    <?php $keyboardShortcutSelectedValue = $keyboardShortcuts[$name]['value'] ?? ''; ?>
                    <?= form_dropdown('key_' . $name, $keyboardShortcutOptions, $keyboardShortcutSelectedValue, 'class="form-select"') ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" name="submit_shortcuts"><?= lang('Common.submit'); ?></button>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    $('#shortcuts_config_form').validate($.extend(form_support.handler, {
        submitHandler: function(form) {
            $(form).ajaxSubmit({
                success: function(response) {
                    $.notify({
                        message: response.message
                    }, {
                        type: response.success ? 'success' : 'danger'
                    });
                },
                error: function(xhr) {
                    const rawMessage = xhr.responseJSON?.message ?? xhr.responseText ?? <?= json_encode(lang('Config.shortcuts_save_error')) ?>;
                    $.notify({
                        message: DOMPurify.sanitize(rawMessage)
                    }, {
                        type: 'danger'
                    });
                },
                dataType: 'json'
            });
        },

        errorLabelContainer: '.shortcuts_error_message_box'
    }));
</script>
