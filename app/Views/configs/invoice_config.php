<?php
/**
 * @var array $invoice_type_options
 * @var array $line_sequence_options
 * @var array $config
 */
?>

<?= form_open('config/saveInvoice/', ['id' => 'invoice_config_form']) ?>

    <?php
    $title_info['config_title'] = lang('Config.invoice_configuration');
    echo view('configs/config_header', $title_info);
    ?>

    <ul id="invoice_error_message_box" class="error_message_box"></ul>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="invoice_enable" name="invoice_enable" value="invoice_enable" <?= $config['invoice_enable'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="invoice_enable"><?= lang('Config.invoice_enable'); ?></label>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="invoice_type" class="form-label"><?= lang('Config.invoice_type'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-file-code"></i></span>
                <select class="form-select" name="invoice_type">
                    <?php foreach ($invoice_type_options as $key => $value): ?>
                        <option value="<?= $key ?>" <?= $key == $config['invoice_type'] ? 'selected' : '' ?>><?= $value ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="recv_invoice_format" class="form-label"><?= lang('Config.recv_invoice_format'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-braces"></i></span>
                <input type="text" class="form-control" name="recv_invoice_format" id="recv_invoice_format" value="<?= $config['recv_invoice_format']; ?>">
            </div>
        </div>
    </div>

    <label for="invoice_default_comments" class="form-label"><?= lang('Config.invoice_default_comments'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-file-text"></i></span>
        <textarea class="form-control" name="invoice_default_comments" id="invoice_default_comments" rows="10" required><?= $config['invoice_default_comments']; ?></textarea>
    </div>

    <label for="invoice_email_message" class="form-label"><?= lang('Config.invoice_email_message'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-envelope-paper"></i></span>
        <textarea class="form-control" name="invoice_email_message" id="invoice_email_message" rows="10" required><?= $config['invoice_email_message']; ?></textarea>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="line_sequence" class="form-label"><?= lang('Config.line_sequence'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-list-ol"></i></span>
                <select class="form-select" name="line_sequence">
                <?php foreach ($line_sequence_options as $key => $value): ?>
                    <option value="<?= $key ?>" <?= $key == $config['line_sequence'] ? 'selected' : '' ?>><?= $value ?></option>
                <?php endforeach; ?>
            </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="sales_invoice_format" class="form-label"><?= lang('Config.sales_invoice_format'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-braces"></i></span>
                <input type="text" class="form-control" name="sales_invoice_format" id="sales_invoice_format" value="<?= $config['sales_invoice_format']; ?>">
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="last_used_invoice_number" class="form-label"><?= lang('Config.last_used_invoice_number'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-123"></i></span>
                <input type="number" class="form-control" name="last_used_invoice_number" id="last_used_invoice_number" value="<?= $config['last_used_invoice_number']; ?>">
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="sales_quote_format" class="form-label"><?= lang('Config.sales_quote_format'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-braces"></i></span>
                <input type="text" class="form-control" name="sales_quote_format" id="sales_quote_format" value="<?= $config['sales_quote_format']; ?>">
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="last_used_quote_number" class="form-label"><?= lang('Config.last_used_quote_number'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-123"></i></span>
                <input type="number" class="form-control" name="last_used_quote_number" id="last_used_quote_number" value="<?= $config['last_used_quote_number']; ?>">
            </div>
        </div>
    </div>

    <label for="quote_default_comments" class="form-label"><?= lang('Config.quote_default_comments'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-file-text"></i></span>
        <textarea class="form-control" name="quote_default_comments" id="quote_default_comments" rows="10" required><?= $config['quote_default_comments']; ?></textarea>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="work_order_enable" name="work_order_enable" value="work_order_enable" <?= $config['work_order_enable'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="work_order_enable"><?= lang('Config.work_order_enable'); ?></label>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="work_order_format" class="form-label"><?= lang('Config.work_order_format'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-braces"></i></span>
                <input type="text" class="form-control" name="work_order_format" id="work_order_format" value="<?= $config['work_order_format']; ?>">
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="last_used_work_order_number" class="form-label"><?= lang('Config.last_used_work_order_number'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-123"></i></span>
                <input type="number" class="form-control" name="last_used_work_order_number" id="last_used_work_order_number" value="<?= $config['last_used_work_order_number']; ?>">
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button class="btn btn-primary" type="submit" name="submit_invoice"><?= lang('Common.submit'); ?></button>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        var enable_disable_invoice_enable = (function() {
            var invoice_enabled = $("#invoice_enable").is(":checked");
            var work_order_enabled = $("#work_order_enable").is(":checked");
            $("#sales_invoice_format, #recv_invoice_format, #invoice_default_comments, #invoice_email_message, select[name='invoice_type'], #sales_quote_format, select[name='line_sequence'], #last_used_invoice_number, #last_used_quote_number, #quote_default_comments, #work_order_enable, #work_order_format, #last_used_work_order_number").prop("disabled", !invoice_enabled);
            if (invoice_enabled) {
                $("#work_order_format, #last_used_work_order_number").prop("disabled", !work_order_enabled);
            } else {
                $("#work_order_enable").attr('checked', false);
            }
            return arguments.callee;
        })();

        var enable_disable_work_order_enable = (function() {
            var work_order_enabled = $("#work_order_enable").is(":checked");
            var invoice_enabled = $("#invoice_enable").is(":checked");
            if (invoice_enabled) {
                $("#work_order_format, #last_used_work_order_number").prop("disabled", !work_order_enabled);
            }
            return arguments.callee;
        })();

        $("#invoice_enable").change(enable_disable_invoice_enable);

        $("#work_order_enable").change(enable_disable_work_order_enable);

        $("#invoice_config_form").validate($.extend(form_support.handler, {

            errorLabelContainer: "#invoice_error_message_box",

            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    beforeSerialize: function(arr, $form, options) {
                        $("#sales_invoice_format, #sales_quote_format, #recv_invoice_format, #invoice_default_comments, #invoice_email_message, #last_used_invoice_number, #last_used_quote_number, #quote_default_comments, #work_order_enable, #work_order_format, #last_used_work_order_number").prop("disabled", false);
                        return true;
                    },
                    success: function(response) {
                        $.notify({
                            icon: 'bi bi-bell-fill',
                            message: response.message
                        }, {
                            type: response.success ? 'success' : 'danger'
                        })
                        // Set back disabled state
                        enable_disable_invoice_enable();
                        enable_disable_work_order_enable();
                    },
                    dataType: 'json'
                });
            }
        }));
    });
</script>
