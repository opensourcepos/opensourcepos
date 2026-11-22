<?php

use Config\OSPOS;

/**
 * @var object $item_kit_info
 * @var string $selected_kit_item
 * @var int $selected_kit_item_id
 * @var array $item_kit_items
 * @var string $controller_name
 */
?>

<div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
<ul id="error_message_box" class="error_message_box"></ul>

<?= form_open("item_kits/save/$item_kit_info->item_kit_id", ['id' => 'item_kit_form', 'class' => 'form-horizontal']) ?>
    <fieldset id="item_kit_basic_info">

        <div class="form-group form-group-sm">
            <?= form_label(lang('Item_kits.item_kit_number'), 'item_kit_number', ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-8">
                <div class="input-group">
                    <span class="input-group-addon input-sm"><span class="glyphicon glyphicon-barcode"></span></span>
                    <?= form_input([
                        'name'  => 'item_kit_number',
                        'id'    => 'item_kit_number',
                        'class' => 'form-control input-sm',
                        'value' => $item_kit_info->item_kit_number
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Item_kits.name'), 'name', ['class' => 'control-label col-xs-3 text-danger required']) ?>
            <div class="col-xs-8">
                <?= form_input([
                    'name'  => 'name',
                    'id'    => 'name',
                    'class' => 'form-control input-sm',
                    'value' => $item_kit_info->name
                ]) ?>
            </div>
        </div>

        <div class="form-group  form-group-sm">
            <?= form_label(lang('Item_kits.find_kit_item'), 'item_name', ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-8">
                <div class="input-group input-group-sm">
                    <?= form_input([
                        'name'  => 'item_name',
                        'id'    => 'item_name',
                        'class' => 'form-control input-sm',
                        'size'  => '50',
                        'value' => $selected_kit_item
                    ]) ?>
                    <?= form_hidden('kit_item_id', (string)$selected_kit_item_id) ?>

                </div>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Item_kits.discount_type'), 'kit_discount_type', ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-8">
                <label class="radio-inline">
                    <?= form_radio([
                        'name'    => 'kit_discount_type',
                        'type'    => 'radio',
                        'value'   => 0,
                        'checked' => (int) $item_kit_info->kit_discount_type === PERCENT
                    ]) ?> <?= lang('Item_kits.discount_percent') ?>
                </label>
                <label class="radio-inline">
                    <?= form_radio([
                        'name'    => 'kit_discount_type',
                        'type'    => 'radio',
                        'value'   => 1,
                        'checked' => (int) $item_kit_info->kit_discount_type === FIXED
                    ]) ?> <?= lang('Item_kits.discount_fixed') ?>
                </label>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Item_kits.discount'), 'kit_discount', ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-3">
                <div class="input-group input-group-sm">
                    <?= form_input([
                        'name'      => 'kit_discount',
                        'size'      => '5',
                        'maxlength' => '5',
                        'id'        => 'kit_discount',
                        'class'     => 'form-control input-sm',
                        'value'     => (int) $item_kit_info->kit_discount_type === FIXED ? to_currency_no_money($item_kit_info->kit_discount) : to_decimals($item_kit_info->kit_discount)
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Item_kits.price_option'), 'price_option', !empty($basic_version) ? ['class' => 'control-label col-xs-3 text-danger required'] : ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-8">
                <label class="radio-inline">
                    <?= form_radio([
                        'name'    => 'price_option',
                        'type'    => 'radio',
                        'value'   => 0,
                        'checked' => (int) $item_kit_info->price_option === PRICE_ALL
                    ]) ?> <?= lang('Item_kits.kit_and_components') ?>
                </label>
                <label class="radio-inline">
                    <?= form_radio([
                        'name'    => 'price_option',
                        'type'    => 'radio',
                        'value'   => 1,
                        'checked' => (int) $item_kit_info->price_option === PRICE_KIT
                    ]) ?> <?= lang('Item_kits.kit_only') ?>
                </label>
                <label class="radio-inline">
                    <?= form_radio([
                        'name'    => 'price_option',
                        'type'    => 'radio',
                        'value'   => 2,
                        'checked' => (int) $item_kit_info->price_option === PRICE_KIT_ITEMS
                    ]) ?> <?= lang('Item_kits.kit_and_stock') ?>
                </label>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Item_kits.print_option'), 'print_option', !empty($basic_version) ? ['class' => 'control-label col-xs-3 text-danger required'] : ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-8">
                <label class="radio-inline">
                    <?= form_radio([
                        'name'    => 'print_option',
                        'type'    => 'radio',
                        'value'   => 0,
                        'checked' => (int) $item_kit_info->print_option === PRINT_ALL
                    ]) ?> <?= lang('Item_kits.all') ?>
                </label>
                <label class="radio-inline">
                    <?= form_radio([
                        'name'    => 'print_option',
                        'type'    => 'radio',
                        'value'   => 1,
                        'checked' => (int) $item_kit_info->print_option === PRINT_PRICED
                    ]) ?> <?= lang('Item_kits.priced_only') ?>
                </label>
                <label class="radio-inline">
                    <?= form_radio([
                        'name'    => 'print_option',
                        'type'    => 'radio',
                        'value'   => 2,
                        'checked' => (int) $item_kit_info->print_option === PRINT_KIT
                    ]) ?> <?= lang('Item_kits.kit_only') ?>
                </label>
            </div>
        </div>


        <div class="form-group form-group-sm">
            <?= form_label(lang('Item_kits.description'), 'description', ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-8">
                <?= form_textarea([
                    'name'  => 'description',
                    'id'    => 'description',
                    'class' => 'form-control input-sm',
                    'value' => $item_kit_info->description
                ]) ?>
            </div>
        </div>

        <div class="form-group form-group-sm">
            <?= form_label(lang('Item_kits.add_item'), 'item', ['class' => 'control-label col-xs-3']) ?>
            <div class="col-xs-8">
                <?= form_input([
                    'name'  => 'item',
                    'id'    => 'item',
                    'class' => 'form-control input-sm'
                ]) ?>
            </div>
        </div>

        <table id="item_kit_items" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th style="width: 10%;"><?= lang('Common.delete') ?></th>
                    <th style="width: 10%;"><?= lang('Item_kits.sequence') ?></th>
                    <th style="width: 60%;"><?= lang('Item_kits.item') ?></th>
                    <th style="width: 20%;"><?= lang('Item_kits.quantity') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($item_kit_items as $item_kit_item) { ?>
                    <tr>
                        <td><a href="#" onclick="return delete_item_kit_row(this);"><span class="glyphicon glyphicon-trash"></span></a></td>
                        <td><input class="quantity form-control input-sm" id="item_seq_<?= $item_kit_item['item_id'] ?>" name="item_kit_seq[<?= $item_kit_item['item_id'] ?>]" value="<?= parse_decimals($item_kit_item['kit_sequence'], 0) ?>"></td>
                        <td><?= esc($item_kit_item['name']) ?></td>
                        <td><input class="quantity form-control input-sm" id="item_qty_<?= $item_kit_item['item_id'] ?>" name="item_kit_qty[<?= $item_kit_item['item_id'] ?>]" value="<?= to_quantity_decimals($item_kit_item['quantity']) ?>"></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

    </fieldset>
<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        <?php
        $config = config(OSPOS::class)->settings;
        $fmt = new NumberFormatter($config['number_locale'], NumberFormatter::DECIMAL);
        $decimal_separator = $fmt->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
        $grouping_separator = $fmt->getSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL);
        $bcp47_locale = str_replace('_', '-', $config['number_locale']);
        if (strpos($bcp47_locale, '@') !== false) {
            [$base, $keywords] = explode('@', $bcp47_locale, 2);
            $key_map = [
                'numbers'   => 'nu',
                'calendar'  => 'ca',
                'collation' => 'co',
                'currency'  => 'cu',
                'timezone'  => 'tz',
                'hours'     => 'hc',
            ];
            $u_extensions = [];
            foreach (explode(';', $keywords) as $keyword) {
                if (strpos($keyword, '=') !== false) {
                    [$key, $val] = explode('=', $keyword, 2);
                    $bcp_key = $key_map[strtolower($key)] ?? strtolower($key);
                    $u_extensions[] = $bcp_key . '-' . strtolower($val);
                }
            }
            $bcp47_locale = $base . (!empty($u_extensions) ? '-u-' . implode('-', $u_extensions) : '');
        }
        ?>
        const decimalSeparator = <?= json_encode($decimal_separator) ?>;
        const groupingSeparator = <?= json_encode($grouping_separator) ?>;
        let numberLocale = <?= json_encode($bcp47_locale) ?>;
        const qtyDecimals = <?= quantity_decimals() ?>;
        const useGrouping = <?= !empty($config['thousands_separator']) ? 'true' : 'false' ?>;
        let qtyFormatter;
        const baseLocale = numberLocale ? numberLocale.split('-u-')[0] : 'en';
        try {
            qtyFormatter = new Intl.NumberFormat(numberLocale, {
                minimumFractionDigits: qtyDecimals,
                maximumFractionDigits: qtyDecimals,
                useGrouping: useGrouping
            });
        } catch (e) {
            try {
                numberLocale = baseLocale;
                qtyFormatter = new Intl.NumberFormat(numberLocale, {
                    minimumFractionDigits: qtyDecimals,
                    maximumFractionDigits: qtyDecimals,
                    useGrouping: useGrouping
                });
            } catch (e2) {
                numberLocale = 'en';
                qtyFormatter = new Intl.NumberFormat('en', {
                    minimumFractionDigits: qtyDecimals,
                    maximumFractionDigits: qtyDecimals,
                    useGrouping: useGrouping
                });
            }
        }

        let browserGrouping = '';
        let browserDecimal = '';
        if (typeof qtyFormatter.formatToParts === 'function') {
            const parts = qtyFormatter.formatToParts(1000.1);
            for (const part of parts) {
                if (part.type === 'group') {
                    browserGrouping = part.value;
                } else if (part.type === 'decimal') {
                    browserDecimal = part.value;
                }
            }
        }

        const digitMap = {};
        let digitFormatter = qtyFormatter;
        try {
            digitFormatter = new Intl.NumberFormat(numberLocale, { useGrouping: false });
        } catch (e) {
            digitFormatter = qtyFormatter;
        }
        for (let i = 0; i <= 9; i++) {
            digitMap[digitFormatter.format(i)] = String(i);
        }

        const parseLocaleQty = function(val) {
            let clean = String(val);
            for (const [localizedDigit, asciiDigit] of Object.entries(digitMap)) {
                if (localizedDigit !== asciiDigit) {
                    clean = clean.split(localizedDigit).join(asciiDigit);
                }
            }
            clean = clean.replace(/[\u0660-\u0669]/g, function(d) {
                return String(d.charCodeAt(0) - 1632);
            }).replace(/[\u06F0-\u06F9]/g, function(d) {
                return String(d.charCodeAt(0) - 1776);
            });
            if (browserGrouping) {
                clean = clean.split(browserGrouping).join('');
            }
            if (groupingSeparator && groupingSeparator !== browserGrouping) {
                clean = clean.split(groupingSeparator).join('');
            }
            const decSep = browserDecimal || decimalSeparator;
            if (decSep && decSep !== '.') {
                clean = clean.split(decSep).join('.');
            }
            if (decimalSeparator && decimalSeparator !== '.' && decimalSeparator !== decSep) {
                clean = clean.split(decimalSeparator).join('.');
            }
            clean = clean.trim();
            if (clean === '' || isNaN(clean)) {
                return val;
            }
            const parsed = parseFloat(clean);
            return isFinite(parsed) ? parsed : val;
        };

        $('#item').autocomplete({
            source: '<?= "items/suggest" ?>',
            minChars: 0,
            autoFocus: false,
            delay: 10,
            appendTo: '.modal-content',
            select: function(e, ui) {
                if ($('#item_qty_' + ui.item.value).length == 1) {
                    const currentQty = parseLocaleQty($('#item_qty_' + ui.item.value).val());
                    if (typeof currentQty === 'number' && !isNaN(currentQty)) {
                        $('#item_qty_' + ui.item.value).val(qtyFormatter.format(currentQty + 1));
                    }
                } else {
                    $('#item_kit_items').append('<tr>' +
                        '<td><a href="#" onclick="return delete_item_kit_row(this);"><span class="glyphicon glyphicon-trash"></span></a></td>' +
                        '<td><input class="quantity form-control input-sm" id="item_seq_' + ui.item.value + '" name="item_kit_seq[' + ui.item.value + ']" value="0"></td>' +
                        '<td>' + DOMPurify.sanitize(ui.item.label) + '</td>' +
                        '<td><input class="quantity form-control input-sm" id="item_qty_' + ui.item.value + '" name="item_kit_qty[' + ui.item.value + ']" value="' + qtyFormatter.format(1) + '"></td>' +
                        '</tr>');
                }
                $('#item').val('');
                return false;
            }
        });

        $("input[name='item_name']").change(function() {
            if (!$("input[name='item_name']").val()) {
                $("input[name='kit_item_id']").val('');
            }
        });

        const fill_value = function(event, ui) {
            event.preventDefault();
            $("input[name='kit_item_id']").val(ui.item.value);
            $("input[name='item_name']").val(DOMPurify.sanitize(ui.item.label));
        };


        $('#item_name').autocomplete({
            source: "<?= 'items/suggestKits' ?>",
            minChars: 0,
            delay: 15,
            cacheLength: 1,
            appendTo: '.modal-content',
            select: fill_value,
            focus: fill_value
        });

        $('#item_kit_form').validate($.extend({
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response) {
                        dialog_support.hide();
                        table_support.handle_submit("<?= esc($controller_name) ?>", response);
                    },
                    dataType: 'json'
                });
            },

            errorLabelContainer: '#error_message_box',

            rules: {
                name: 'required',
                category: 'required',
                item_kit_number: {
                    required: false,
                    remote: {
                        url: '<?= esc("$controller_name/checkItemNumber") ?>',
                        type: 'POST',
                        data: {
                            'item_kit_id': "<?= $item_kit_info->item_kit_id ?>",
                            'item_kit_number': function() {
                                return $('#item_kit_number').val();
                            }
                        }
                    }
                }
            },

            messages: {
                name: "<?= lang('Items.name_required') ?>",
                category: "<?= lang('Items.category_required') ?>",
                item_kit_number: "<?= lang('Item_kits.item_kit_number_duplicate') ?>"
            }
        }, form_support.error));
    });

    function delete_item_kit_row(link) {
        $(link).parent().parent().remove();
        return false;
    }
</script>
