<?php
/**
 * @var array $config
 */
?>
<?= form_open('config/saveReceipt/', ['id' => 'receipt_config_form']) ?>

    <?php
    $title_info['config_title'] = lang('Config.receipt_configuration');
    echo view('configs/config_header', $title_info);
    ?>

    <ul id="receipt_error_message_box" class="error_message_box"></ul>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="receipt_template" class="form-label"><?= lang('Config.receipt_template'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-file-code"></i></span>
                <select class="form-select" name="receipt_template">
                    <option value="receipt_default" <?= $config['receipt_template'] == 'receipt_default' ? 'selected' : '' ?>><?= lang('Config.receipt_default') ?></option>
                    <option value="receipt_short" <?= $config['receipt_template'] == 'receipt_short' ? 'selected' : '' ?>><?= lang('Config.receipt_short') ?></option>
                </select>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <label for="receipt_font_size" class="form-label"><?= lang('Config.receipt_font_size'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-arrows-angle-expand"></i></span>
                <input type="number" class="form-control" name="receipt_font_size" id="receipt_font_size" value="<?= $config['receipt_font_size']; ?>">
                <span class="input-group-text">px</span>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <label for="print_delay_autoreturn" class="form-label"><?= lang('Config.print_delay_autoreturn'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-stopwatch"></i></span>
                <input type="number" class="form-control" name="print_delay_autoreturn" id="print_delay_autoreturn" value="<?= $config['print_delay_autoreturn']; ?>">
                <span class="input-group-text">s</span>
            </div>
        </div>
    </div>

    <label for="email_receipt_check_behaviour" class="form-label"><?= lang('Config.email_receipt_check_behaviour'); ?></label>
    <div class="row mb-3">
        <div class="col-12">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="email_receipt_check_behaviour" id="email_receipt_check_behaviour_always" value="always" <?= $config['email_receipt_check_behaviour'] == 'always' ? 'checked' : '' ?>>
                <label class="form-check-label" for="email_receipt_check_behaviour_always"><?= lang('Config.email_receipt_check_behaviour_always') ?></label>
            </div>

            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="email_receipt_check_behaviour" id="email_receipt_check_behaviour_never" value="never" <?= $config['email_receipt_check_behaviour'] == 'never' ? 'checked' : '' ?>>
                <label class="form-check-label" for="email_receipt_check_behaviour_never"><?= lang('Config.email_receipt_check_behaviour_never') ?></label>
            </div>

            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="email_receipt_check_behaviour" id="email_receipt_check_behaviour_last" value="last" <?= $config['email_receipt_check_behaviour'] == 'last' ? 'checked' : '' ?>>
                <label class="form-check-label" for="email_receipt_check_behaviour_last"><?= lang('Config.email_receipt_check_behaviour_last') ?></label>
            </div>
        </div>
    </div>

    <label for="print_receipt_check_behaviour" class="form-label"><?= lang('Config.print_receipt_check_behaviour'); ?></label>
    <div class="row mb-3">
        <div class="col-12">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="print_receipt_check_behaviour" id="print_receipt_check_behaviour_always" value="always" <?= $config['print_receipt_check_behaviour'] == 'always' ? 'checked' : '' ?>>
                <label class="form-check-label" for="print_receipt_check_behaviour_always"><?= lang('Config.print_receipt_check_behaviour_always') ?></label>
            </div>

            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="print_receipt_check_behaviour" id="print_receipt_check_behaviour_never" value="never" <?= $config['print_receipt_check_behaviour'] == 'never' ? 'checked' : '' ?>>
                <label class="form-check-label" for="print_receipt_check_behaviour_never"><?= lang('Config.print_receipt_check_behaviour_never') ?></label>
            </div>

            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="print_receipt_check_behaviour" id="print_receipt_check_behaviour_last" value="last" <?= $config['print_receipt_check_behaviour'] == 'last' ? 'checked' : '' ?>>
                <label class="form-check-label" for="print_receipt_check_behaviour_last"><?= lang('Config.print_receipt_check_behaviour_last') ?></label>
            </div>
        </div>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="receipt_show_company_name" name="receipt_show_company_name" value="receipt_show_company_name" <?= $config['receipt_show_company_name'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="receipt_show_company_name"><?= lang('Config.receipt_show_company_name'); ?></label>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="receipt_show_taxes" name="receipt_show_taxes" value="receipt_show_taxes" <?= $config['receipt_show_taxes'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="receipt_show_taxes"><?= lang('Config.receipt_show_taxes'); ?></label>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="receipt_show_tax_ind" name="receipt_show_tax_ind" value="receipt_show_tax_ind" <?= $config['receipt_show_tax_ind'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="receipt_show_tax_ind"><?= lang('Config.receipt_show_tax_ind'); ?></label>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="receipt_show_total_discount" name="receipt_show_total_discount" value="receipt_show_total_discount" <?= $config['receipt_show_total_discount'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="receipt_show_total_discount"><?= lang('Config.receipt_show_total_discount'); ?></label>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="receipt_show_description" name="receipt_show_description" value="receipt_show_description" <?= $config['receipt_show_description'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="receipt_show_description"><?= lang('Config.receipt_show_description'); ?></label>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="receipt_show_serialnumber" name="receipt_show_serialnumber" value="receipt_show_serialnumber" <?= $config['receipt_show_serialnumber'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="receipt_show_serialnumber"><?= lang('Config.receipt_show_serialnumber'); ?></label>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="print_silently" name="print_silently" value="print_silently" <?= $config['print_silently'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="print_silently"><?= lang('Config.print_silently'); ?></label>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="print_header" name="print_header" value="print_header" <?= $config['print_header'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="print_header"><?= lang('Config.print_header'); ?></label>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="print_footer" name="print_footer" value="print_footer" <?= $config['print_footer'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="print_footer"><?= lang('Config.print_footer'); ?></label>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="receipt_printer" class="form-label"><?= lang('Config.receipt_printer'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-printer"></i></span>
                <select class="form-select" name="receipt_printer" id="receipt_printer"></select>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="invoice_printer" class="form-label"><?= lang('Config.invoice_printer'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-printer"></i></span>
                <select class="form-select" name="invoice_printer" id="invoice_printer"></select>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="takings_printer" class="form-label"><?= lang('Config.takings_printer'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-printer"></i></span>
                <select class="form-select" name="takings_printer" id="takings_printer"></select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-6 col-lg-3">
            <label for="print_top_margin" class="form-label"><?= lang('Config.print_top_margin'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-arrow-bar-down"></i></span>
                <input type="number" class="form-control" name="print_top_margin" id="print_top_margin" value="<?= $config['print_top_margin']; ?>">
                <span class="input-group-text">px</span>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <label for="print_left_margin" class="form-label"><?= lang('Config.print_left_margin'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-arrow-bar-right"></i></span>
                <input type="number" class="form-control" name="print_left_margin" id="print_left_margin" value="<?= $config['print_left_margin']; ?>">
                <span class="input-group-text">px</span>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <label for="print_bottom_margin" class="form-label"><?= lang('Config.print_bottom_margin'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-arrow-bar-up"></i></span>
                <input type="number" class="form-control" name="print_bottom_margin" id="print_bottom_margin" value="<?= $config['print_bottom_margin']; ?>">
                <span class="input-group-text">px</span>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <label for="print_right_margin" class="form-label"><?= lang('Config.print_right_margin'); ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-arrow-bar-left"></i></span>
                <input type="number" class="form-control" name="print_right_margin" id="print_right_margin" value="<?= $config['print_right_margin']; ?>">
                <span class="input-group-text">px</span>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button class="btn btn-primary" type="submit" name="submit_receipt"><?= lang('Common.submit'); ?></button>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        if (window.localStorage && window.jsPrintSetup) {
            var printers = (jsPrintSetup.getPrintersList() && jsPrintSetup.getPrintersList().split(',')) || [];
            $('#receipt_printer, #invoice_printer, #takings_printer').each(function() {
                var $this = $(this)
                $(printers).each(function(key, value) {
                    $this.append($('<option>', {
                        value: value
                    }).text(value));
                });
                $("option[value='" + localStorage[$(this).attr('id')] + "']", this).prop('selected', true);
                $(this).change(function() {
                    localStorage[$(this).attr('id')] = $(this).val();
                });
            });
        } else {
            $("input[id*='margin'], #print_footer, #print_header, #receipt_printer, #invoice_printer, #takings_printer, #print_silently").prop('disabled', true);
            $("#receipt_printer, #invoice_printer, #takings_printer").each(function() {
                $(this).append($('<option>', {
                    value: 'na'
                }).text('N/A'));
            });
        }

        var dialog_confirmed = window.jsPrintSetup;

        $('#receipt_config_form').validate($.extend(form_support.handler, {
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    beforeSerialize: function(arr, $form, options) {
                        return (dialog_confirmed || confirm('<?= lang('Config.jsprintsetup_required') ?>'));
                    },
                    success: function(response) {
                        $.notify({
                            icon: 'bi bi-bell-fill',
                            message: response.message
                        }, {
                            type: response.success ? 'success' : 'danger'
                        })
                    },
                    dataType: 'json'
                });
            },

            errorLabelContainer: "#receipt_error_message_box",

            rules: {
                print_top_margin: {
                    required: true,
                    number: true
                },
                print_left_margin: {
                    required: true,
                    number: true
                },
                print_bottom_margin: {
                    required: true,
                    number: true
                },
                print_right_margin: {
                    required: true,
                    number: true
                },
                receipt_font_size: {
                    required: true,
                    number: true
                },
                print_delay_autoreturn: {
                    required: true,
                    number: true
                }
            },

            messages: {
                print_top_margin: {
                    required: "<?= lang('Config.print_top_margin_required') ?>",
                    number: "<?= lang('Config.print_top_margin_number') ?>"
                },
                print_left_margin: {
                    required: "<?= lang('Config.print_left_margin_required') ?>",
                    number: "<?= lang('Config.print_left_margin_number') ?>"
                },
                print_bottom_margin: {
                    required: "<?= lang('Config.print_bottom_margin_required') ?>",
                    number: "<?= lang('Config.print_bottom_margin_number') ?>"
                },
                print_right_margin: {
                    required: "<?= lang('Config.print_right_margin_required') ?>",
                    number: "<?= lang('Config.print_right_margin_number') ?>"
                },
                receipt_font_size: {
                    required: "<?= lang('Config.receipt_font_size_required') ?>",
                    number: "<?= lang('Config.receipt_font_size_number') ?>"
                },
                print_delay_autoreturn: {
                    required: "<?= lang('Config.print_delay_autoreturn_required') ?>",
                    number: "<?= lang('Config.print_delay_autoreturn_number') ?>"
                }
            }
        }));
    });
</script>
