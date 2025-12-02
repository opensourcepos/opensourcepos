<?php
/**
 * @var array $tax_code_options
 * @var array $tax_category_options
 * @var array $tax_jurisdiction_options
 * @var string $controller_name
 * @var array $config
 */
?>

<?= form_open('config/saveTax/', ['id' => 'tax_config_form']) ?>

    <?php
    $title_info['config_title'] = lang('Config.tax_configuration');
    echo view('configs/config_header', $title_info);
    ?>

    <ul id="tax_error_message_box" class="error_message_box"></ul>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="tax_id" class="form-label"><?= lang('Config.tax_id'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-bank"></i></span>
                <input type="text" name="tax_id" class="form-control" id="tax_id" value="<?= $config['tax_id']; ?>">
            </div>
        </div>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="tax_included" name="tax_included" <?= $config['tax_included'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="tax_included"><?= lang('Config.tax_included'); ?></label>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="default_tax_1_rate" class="form-label"><?= lang('Config.default_tax_rate_1') ?></label>
            <div class="input-group mb-3">
                <label class="input-group-text"><i class="bi bi-1-square"></i></label>
                <input type="text" class="form-control w-25" id="default_tax_1_name" name="default_tax_1_name" value="<?= $config['default_tax_1_name'] !== false ? $config['default_tax_1_name'] : lang('Items.sales_tax_1') ?>">
                <input type="text" class="form-control" id="default_tax_1_rate" name="default_tax_1_rate" value="<?= to_tax_decimals($config['default_tax_1_rate']) ?>">
                <label class="input-group-text"><i class="bi bi-percent"></i></label>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="default_tax_2_rate" class="form-label"><?= lang('Config.default_tax_rate_2') ?></label>
            <div class="input-group mb-3">
                <label class="input-group-text"><i class="bi bi-2-square"></i></label>
                <input type="text" class="form-control w-25" id="default_tax_2_name" name="default_tax_2_name" value="<?= $config['default_tax_2_name'] !== false ? $config['default_tax_2_name'] : lang('Items.sales_tax_2') ?>">
                <input type="text" class="form-control" id="default_tax_2_rate" name="default_tax_2_rate" value="<?= to_tax_decimals($config['default_tax_2_rate']) ?>">
                <label class="input-group-text"><i class="bi bi-percent"></i></label>
            </div>
        </div>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="use_destination_based_tax" name="use_destination_based_tax" value="use_destination_based_tax" <?= $config['use_destination_based_tax'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="use_destination_based_tax"><?= lang('Config.use_destination_based_tax'); ?></label>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="default_tax_code" class="form-label"><?= lang('Config.default_tax_code'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-code"></i></span>
                <select class="form-select" name="default_tax_code" id="default_tax_code">
                    <?php foreach ($tax_code_options as $key => $value): ?>
                        <option value="<?= $key ?>" <?= $config['default_tax_code'] == $key ? 'selected' : '' ?>><?= $value ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="default_tax_category" class="form-label"><?= lang('Config.default_tax_category'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-bookmark"></i></span>
                <select class="form-select" name="default_tax_category" id="default_tax_category">
                    <?php foreach ($tax_category_options as $key => $value): ?>
                        <option value="<?= $key ?>" <?= $config['default_tax_category'] == $key ? 'selected' : '' ?>><?= $value ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="default_tax_jurisdiction" class="form-label"><?= lang('Config.default_tax_jurisdiction'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-globe"></i></span>
                <select class="form-select" name="default_tax_jurisdiction" id="default_tax_jurisdiction">
                    <?php foreach ($tax_jurisdiction_options as $key => $value): ?>
                        <option value="<?= $key ?>" <?= $config['default_tax_jurisdiction'] == $key ? 'selected' : '' ?>><?= $value ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button class="btn btn-primary" type="submit" name="submit_tax"><?= lang('Common.submit'); ?></button>
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
                            icon: 'bi bi-bell-fill',
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
