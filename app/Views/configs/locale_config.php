<?php
/**
 * @var string $currency_code
 * @var array $rounding_options
 * @var string $controller_name
 * @var array $config
 */

 $beta = '<sup><span class="badge bg-secondary">BETA</span></sup>';
?>

<?= form_open('config/saveLocale/', ['id' => 'locale_config_form']) ?>

    <?php
    $title_info['config_title'] = lang('Config.locale_configuration');
    echo view('configs/config_header', $title_info);
    ?>

    <ul id="locale_error_message_box" class="error_message_box"></ul>

    <div class="row">
        <div class="col-12 col-sm-6 col-xxl-3">
            <label for="number_locale" class="form-label"><?= lang('Config.number_locale') ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-globe-americas"></i></span>
                <input type="text" class="form-control" name="number_locale" id="number_locale" value="<?= $config['number_locale'] ?>">
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xxl-3">
            <label for="number_locale_example" class="form-label">Localization Example</label>
            <div class="mb-3" id="number_locale_example">
                <?= to_currency(1234567890.12300) ?>&nbsp;
                <a href="https://github.com/opensourcepos/opensourcepos/wiki/Localisation-support" target="_blank" rel="noopener">
                    <i class="bi bi-link-45deg link-secondary" data-bs-toggle="tooltip" title="<?= lang('Config.number_locale_tooltip'); ?>"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="thousands_separator" name="thousands_separator" value="thousands_separator" <?= $config['thousands_separator'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="thousands_separator"><?= lang('Config.thousands_separator'); ?></label>
    </div>

    <div class="row">
        <div class="col-12 col-sm-6 col-xxl-3">
            <label for="currency_symbol" class="form-label"><?= lang('Config.currency_symbol') ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-currency-exchange"></i></span>
                <input type="text" class="form-control" name="currency_symbol" id="currency_symbol" value="<?= $config['currency_symbol'] ?>">
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xxl-3">
            <label for="currency_code" class="form-label"><?= lang('Config.currency_code') ?></label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-cash"></i></span>
                <input type="text" class="form-control" name="currency_code" id="currency_code" value="<?= $currency_code ?>">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-sm-6 col-xxl-3">
            <label for="currency_decimals" class="form-label"><?= lang('Config.currency_decimals') ?></label>
            <div class="input-group mb-3">
                <label class="input-group-text"><i class="bi bi-coin"></i></label>
                <select class="form-select" name="currency_decimals">
                    <option value="0" <?= $config['currency_decimals'] == '0' ? 'selected' : '' ?>>0</option>
                    <option value="1" <?= $config['currency_decimals'] == '1' ? 'selected' : '' ?>>1</option>
                    <option value="2" <?= $config['currency_decimals'] == '2' ? 'selected' : '' ?>>2</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xxl-3">
            <label for="tax_decimals" class="form-label"><?= lang('Config.tax_decimals') ?></label>
            <div class="input-group mb-3">
                <label class="input-group-text"><i class="bi bi-archive"></i></label>
                <select class="form-select" name="tax_decimals">
                    <option value="0" <?= $config['tax_decimals'] == '0' ? 'selected' : '' ?>>0</option>
                    <option value="1" <?= $config['tax_decimals'] == '1' ? 'selected' : '' ?>>1</option>
                    <option value="2" <?= $config['tax_decimals'] == '2' ? 'selected' : '' ?>>2</option>
                    <option value="3" <?= $config['tax_decimals'] == '3' ? 'selected' : '' ?>>3</option>
                    <option value="4" <?= $config['tax_decimals'] == '4' ? 'selected' : '' ?>>4</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xxl-3">
            <label for="quantity_decimals" class="form-label"><?= lang('Config.quantity_decimals') ?></label>
            <div class="input-group mb-3">
                <label class="input-group-text"><i class="bi bi-123"></i></label>
                <select class="form-select" name="quantity_decimals">
                    <option value="0" <?= $config['quantity_decimals'] == '0' ? 'selected' : '' ?>>0</option>
                    <option value="1" <?= $config['quantity_decimals'] == '1' ? 'selected' : '' ?>>1</option>
                    <option value="2" <?= $config['quantity_decimals'] == '2' ? 'selected' : '' ?>>2</option>
                    <option value="3" <?= $config['quantity_decimals'] == '3' ? 'selected' : '' ?>>3</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xxl-3">
            <label for="cash_decimals" class="form-label"><?= lang('Config.cash_decimals') ?>
                <i class="bi bi-info-circle-fill text-secondary" data-bs-toggle="tooltip" title="<?= lang('Config.cash_decimals_tooltip'); ?>"></i>
            </label>
            <div class="input-group mb-3">
                <label class="input-group-text"><i class="bi bi-cash-coin"></i></label>
                <select class="form-select" name="cash_decimals">
                    <option value="0" <?= $config['cash_decimals'] == '0' ? 'selected' : '' ?>>0</option>
                    <option value="1" <?= $config['cash_decimals'] == '1' ? 'selected' : '' ?>>1</option>
                    <option value="2" <?= $config['cash_decimals'] == '2' ? 'selected' : '' ?>>2</option>
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xxl-3">
            <label for="cash_rounding_code" class="form-label"><?= lang('Config.cash_rounding') ?></label>
            <div class="input-group mb-3">
                <label class="input-group-text"><i class="bi bi-arrow-repeat"></i></label>
                <select class="form-select" name="cash_rounding_code">
                    <?php foreach ($rounding_options as $code => $label): ?>
                        <option value="<?= $code ?>" <?= $config['cash_rounding_code'] == $code ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="payment_options_order" class="form-label"><?= lang('Config.payment_options_order') ?></label>
            <div class="input-group mb-3">
                <label class="input-group-text"><i class="bi bi-credit-card"></i></label>
                <select class="form-select" name="payment_options_order" id="payment_options_order">
                    <option value="cashdebitcredit" <?= $config['payment_options_order'] == 'cashdebitcredit' ? 'selected' : '' ?>><?= lang('Sales.cash') ?> / <?= lang('Sales.debit') ?> / <?= lang('Sales.credit') ?></option>
                    <option value="debitcreditcash" <?= $config['payment_options_order'] == 'debitcreditcash' ? 'selected' : '' ?>><?= lang('Sales.debit') ?> / <?= lang('Sales.credit') ?> / <?= lang('Sales.cash') ?></option>
                    <option value="debitcashcredit" <?= $config['payment_options_order'] == 'debitcashcredit' ? 'selected' : '' ?>><?= lang('Sales.debit') ?> / <?= lang('Sales.cash') ?> / <?= lang('Sales.credit') ?></option>
                    <option value="creditdebitcash" <?= $config['payment_options_order'] == 'creditdebitcash' ? 'selected' : '' ?>><?= lang('Sales.credit') ?> / <?= lang('Sales.debit') ?> / <?= lang('Sales.cash') ?></option>
                    <option value="creditcashdebit" <?= $config['payment_options_order'] == 'creditcashdebit' ? 'selected' : '' ?>><?= lang('Sales.credit') ?> / <?= lang('Sales.cash') ?> / <?= lang('Sales.debit') ?></option>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="country_codes" class="form-label">
                <?= lang('Config.country_codes') ?>
                <a href="https://wiki.openstreetmap.org/wiki/Nominatim/Country_Codes" target="_blank" rel="noopener">
                    <i class="bi bi-link-45deg text-secondary" data-bs-toggle="tooltip" title="<?= lang('Config.country_codes_tooltip'); ?>"></i>
                </a>
            </label>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-code"></i></span>
                <input type="text" class="form-control" name="country_codes" id="country_codes" value="<?= $config['country_codes'] ?>">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="language" class="form-label"><?= lang('Config.language') ?></label>
            <div class="input-group mb-3">
                <label class="input-group-text"><i class="bi bi-translate"></i></label>
                <?= form_dropdown(
                    'language',
                    get_languages(),
                    current_language_code(true) . ':' . current_language(true),
                    ['class' => 'form-select']
                ) ?>
            </div>
        </div>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="rtl_language" name="rtl_language" value="rtl_language" <?= $config['rtl_language'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="rtl_language">RTL Language <?= $beta; ?></label>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="timezone" class="form-label"><?= lang('Config.timezone') ?></label>
            <div class="input-group mb-3">
                <label class="input-group-text"><i class="bi bi-clock"></i></label>
                <?= form_dropdown(
                    'timezone',
                    get_timezones(),
                    $config['timezone'] ? $config['timezone'] : date_default_timezone_get(),
                    ['class' => 'form-select']
                ) ?>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label for="datetimeformat" class="form-label"><?= lang('Config.datetimeformat') ?></label>
            <div class="input-group mb-3">
                <label class="input-group-text"><i class="bi bi-calendar2"></i></label>
                <?= form_dropdown(
                    'dateformat',
                    get_dateformats(),
                    $config['dateformat'],
                    ['class' => 'form-select']
                ) ?>
                <?= form_dropdown(
                    'timeformat',
                    get_timeformats(),
                    $config['timeformat'],
                    ['class' => 'form-select']
                ) ?>
            </div>
        </div>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="date_or_time_format" name="date_or_time_format" value="date_or_time_format" <?= $config['date_or_time_format'] == 1 ? 'checked' : '' ?>>
        <label class="form-check-label" for="date_or_time_format"><?= lang('Config.date_or_time_format'); ?></label>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6">
            <label for="financial_year" class="form-label"><?= lang('Config.financial_year') ?></label>
            <div class="input-group mb-3">
                <label class="input-group-text"><i class="bi bi-calendar2-month"></i></label>
                <select class="form-select" name="financial_year" id="financial_year">
                    <option value="1" <?= ($config['financial_year'] == '1' ? 'selected' : ''); ?>><?= lang('Config.financial_year_jan'); ?></option>
                    <option value="2" <?= ($config['financial_year'] == '2' ? 'selected' : ''); ?>><?= lang('Config.financial_year_feb'); ?></option>
                    <option value="3" <?= ($config['financial_year'] == '3' ? 'selected' : ''); ?>><?= lang('Config.financial_year_mar'); ?></option>
                    <option value="4" <?= ($config['financial_year'] == '4' ? 'selected' : ''); ?>><?= lang('Config.financial_year_apr'); ?></option>
                    <option value="5" <?= ($config['financial_year'] == '5' ? 'selected' : ''); ?>><?= lang('Config.financial_year_may'); ?></option>
                    <option value="6" <?= ($config['financial_year'] == '6' ? 'selected' : ''); ?>><?= lang('Config.financial_year_jun'); ?></option>
                    <option value="7" <?= ($config['financial_year'] == '7' ? 'selected' : ''); ?>><?= lang('Config.financial_year_jul'); ?></option>
                    <option value="8" <?= ($config['financial_year'] == '8' ? 'selected' : ''); ?>><?= lang('Config.financial_year_aug'); ?></option>
                    <option value="9" <?= ($config['financial_year'] == '9' ? 'selected' : ''); ?>><?= lang('Config.financial_year_sep'); ?></option>
                    <option value="10" <?= ($config['financial_year'] == '10' ? 'selected' : ''); ?>><?= lang('Config.financial_year_oct'); ?></option>
                    <option value="11" <?= ($config['financial_year'] == '11' ? 'selected' : ''); ?>><?= lang('Config.financial_year_nov'); ?></option>
                    <option value="12" <?= ($config['financial_year'] == '12' ? 'selected' : ''); ?>><?= lang('Config.financial_year_dec'); ?></option>
                </select>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button class="btn btn-primary" type="submit" name="submit_locale"><?= lang('Common.submit'); ?></button>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $('span').tooltip();

        $('#currency_symbol, #thousands_separator, #currency_code').change(function() {
            var data = {
                number_locale: $('#number_locale').val()
            };
            data['save_number_locale'] = $("input[name='save_number_locale']").val();
            data['currency_symbol'] = $('#currency_symbol').val();
            data['currency_code'] = $('#currency_code').val();
            data['thousands_separator'] = $('#thousands_separator').is(":checked")
            $.post("<?= "$controller_name/checkNumberLocale" ?>",
                data,
                function(response) {
                    $("input[name='save_number_locale']").val(response.save_number_locale);
                    $('#number_locale_example').text(response.number_locale_example);
                    $('#currency_symbol').val(response.currency_symbol);
                    $('#currency_code').val(response.currency_code);
                },
                'json'
            );
        });

        $('#locale_config_form').validate($.extend(form_support.handler, {
            rules: {
                number_locale: {
                    required: true,
                    remote: {
                        url: "<?= "$controller_name/checkNumberLocale" ?>",
                        type: 'POST',
                        data: {
                            'number_locale': function() {
                                return $('#number_locale').val();
                            },
                            'save_number_locale': function() {
                                return $("input[name='save_number_locale']").val();
                            },
                            'currency_symbol': function() {
                                return $('#currency_symbol').val();
                            },
                            'thousands_separator': function() {
                                return $('#thousands_separator').is(':checked');
                            },
                            'currency_code': function() {
                                return $('#currency_code').val();
                            }
                        },
                        dataFilter: function(data) {
                            var response = JSON.parse(data);
                            $("input[name='save_number_locale']").val(response.save_number_locale);
                            $('#number_locale_example').text(response.number_locale_example);
                            $('#currency_symbol').val(response.currency_symbol);
                            $('#currency_code').val(response.currency_code);
                            return response.success;
                        }
                    }
                }
            },

            messages: {
                number_locale: {
                    required: "<?= lang('Config.number_locale_required') ?>",
                    number_locale: "<?= lang('Config.number_locale_invalid') ?>"
                }
            },

            errorLabelContainer: '#locale_error_message_box'
        }));
    });
</script>
