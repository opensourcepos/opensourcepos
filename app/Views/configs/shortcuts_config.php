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

<?= form_open('config/saveShortcuts', ['id' => 'shortcuts_config_form', 'class' => 'form-horizontal']) ?>
    <div id="config_wrapper">
        <div class="row">
            <fieldset id="config_info">
                <div class="col-md-8">
                    <div id="required_fields_message"><?= esc(lang('Common.fields_required_message')) ?></div>
                    <ul id="shortcuts_error_message_box" class="error_message_box"></ul>

                    <?php foreach ($shortcutLabels as $name => $label): ?>
                        <div class="form-group form-group-sm">
                            <?= form_label($label, 'key_' . $name, ['class' => 'control-label col-xs-3']) ?>
                            <div class="col-xs-4">
                                <?php $keyboardShortcutSelectedValue = $keyboardShortcuts[$name]['value'] ?? ''; ?>
                                <?= form_dropdown(
                                    'key_' . $name,
                                    $keyboardShortcutOptions,
                                    $keyboardShortcutSelectedValue,
                                    'class="form-control input-sm"'
                                ) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="col-xs-12 clearfix">
                        <?= form_submit([
                            'name'  => 'submit_shortcuts',
                            'id'    => 'submit_shortcuts',
                            'value' => lang('Common.submit'),
                            'class' => 'btn btn-primary btn-sm pull-right'
                        ]) ?>
                    </div>
                </div>
            </fieldset>
        </div>
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

        errorLabelContainer: '#shortcuts_error_message_box'
    }));
</script>
