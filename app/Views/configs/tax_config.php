<?php
/**
 * @var array $tax_code_options
 * @var array $tax_category_options
 * @var array $tax_jurisdiction_options
 * @var string $controller_name
 * @var array $config
 */
?>

<?= form_open('config/saveTax/', ['id' => 'tax_config_form', 'class' => 'form-horizontal']) ?>
    <div id="config_wrapper">
        <fieldset id="config_info">

            <div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
            <ul id="tax_error_message_box" class="error_message_box"></ul>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.tax_id'), 'tax_id', ['class' => 'control-label col-xs-2']) ?>
                <div class="col-xs-2">
                    <?= form_input([
                        'name'  => 'tax_id',
                        'id'    => 'tax_id',
                        'class' => 'form-control input-sm',
                        'value' => $config['tax_id']
                    ]) ?>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.tax_included'), 'tax_included', ['class' => 'control-label col-xs-2']) ?>
                <div class="col-xs-2">
                    <?= form_checkbox([
                        'name'    => 'tax_included',
                        'id'      => 'tax_included',
                        'value'   => 'tax_included',
                        'checked' => $config['tax_included'] == 1
                    ]) ?>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.default_tax_rate_1'), 'default_tax_1_rate', ['class' => 'control-label col-xs-2']) ?>
                <div class="col-xs-2">
                    <?= form_input([
                        'name'  => 'default_tax_1_name',
                        'id'    => 'default_tax_1_name',
                        'class' => 'form-control input-sm',
                        'value' => $config['default_tax_1_name'] !== false ? $config['default_tax_1_name'] : lang('Items.sales_tax_1')
                    ]) ?>
                </div>
                <div class="col-xs-1 input-group">
                    <?= form_input([
                        'name'  => 'default_tax_1_rate',
                        'id'    => 'default_tax_1_rate',
                        'class' => 'form-control input-sm',
                        'value' => to_tax_decimals($config['default_tax_1_rate'])
                    ]) ?>
                    <span class="input-group-addon input-sm">%</span>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.default_tax_rate_2'), 'default_tax_2_rate', ['class' => 'control-label col-xs-2']) ?>
                <div class="col-xs-2">
                    <?= form_input([
                        'name'  => 'default_tax_2_name',
                        'id'    => 'default_tax_2_name',
                        'class' => 'form-control input-sm',
                        'value' => $config['default_tax_2_name'] !== false ? $config['default_tax_2_name'] : lang('Items.sales_tax_2')
                    ]) ?>
                </div>
                <div class="col-xs-1 input-group">
                    <?= form_input([
                        'name'  => 'default_tax_2_rate',
                        'id'    => 'default_tax_2_rate',
                        'class' => 'form-control input-sm',
                        'value' => to_tax_decimals($config['default_tax_2_rate'])
                    ]) ?>
                    <span class="input-group-addon input-sm">%</span>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.use_destination_based_tax'), 'use_destination_based_tax', ['class' => 'control-label col-xs-2']) ?>
                <div class="col-xs-2">
                    <?= form_checkbox([
                        'name'    => 'use_destination_based_tax',
                        'id'      => 'use_destination_based_tax',
                        'value'   => 'use_destination_based_tax',
                        'checked' => $config['use_destination_based_tax'] == 1
                    ]) ?>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.default_tax_code'), 'default_tax_code', ['class' => 'control-label col-xs-2']) ?>
                <div class="col-xs-2">
                    <?= form_dropdown(
                        'default_tax_code',
                        $tax_code_options,
                        $config['default_tax_code'],
                        'class="form-control input-sm"'
                    ) ?>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.default_tax_category'), 'default_tax_category', ['class' => 'control-label col-xs-2']) ?>
                <div class="col-xs-2">
                    <?= form_dropdown(
                        'default_tax_category',
                        $tax_category_options,
                        $config['default_tax_category'],
                        'class="form-control input-sm"'
                    ) ?>
                </div>
            </div>

            <div class="form-group form-group-sm">
                <?= form_label(lang('Config.default_tax_jurisdiction'), 'default_tax_jurisdiction', ['class' => 'control-label col-xs-2']) ?>
                <div class="col-xs-2">
                    <?= form_dropdown(
                        'default_tax_jurisdiction',
                        $tax_jurisdiction_options,
                        $config['default_tax_jurisdiction'],
                        'class="form-control input-sm"'
                    ) ?>
                </div>
            </div>

            <?= form_submit([
                'name'  => 'submit_tax',
                'id'    => 'submit_tax',
                'value' => lang('Common.submit'),
                'class' => 'btn btn-primary btn-sm pull-right'
            ]) ?>

        </fieldset>
    </div>
<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        var enable_disable_use_destination_based_tax = (function() {
            var use_destination_based_tax = $("#use_destination_based_tax").is(":checked");
            $("select[name='default_tax_code']").prop("disabled", !use_destination_based_tax);
            $("select[name='default_tax_category']").prop("disabled", !use_destination_based_tax);
            $("select[name='default_tax_jurisdiction']").prop("disabled", !use_destination_based_tax);
            $("input[name='tax_included']").prop("disabled", use_destination_based_tax);
            $("input[name='default_tax_1_rate']").prop("disabled", use_destination_based_tax);
            $("input[name='default_tax_1_name']").prop("disabled", use_destination_based_tax);
            $("input[name='default_tax_2_rate']").prop("disabled", use_destination_based_tax);
            $("input[name='default_tax_2_name']").prop("disabled", use_destination_based_tax);

            return arguments.callee;
        })();

        $("#use_destination_based_tax").change(enable_disable_use_destination_based_tax);


        $('#tax_config_form').validate($.extend(form_support.handler, {
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    beforeSerialize: function(arr, $form, options) {
                        return true;
                    },
                    success: function(response) {
                        $.notify({
                            message: response.message
                        }, {
                            type: response.success ? 'success' : 'danger'
                        });
                    },
                    dataType: 'json'
                });
            },

            rules: {
                default_tax_1_rate: {
                    remote: "<?= "$controller_name/checkNumeric" ?>"
                },
                default_tax2_rate: {
                    remote: "<?= "$controller_name/checkNumeric" ?>"
                },
            },

            messages: {
                default_tax_1_rate: {
                    number: "<?= lang('Config.default_tax_rate_number') ?>"
                },
            }
        }));
    });
</script>
