<?php
/**
 * @var int $giftcard_id
 * @var string $selected_person_name
 * @var int $selected_person_id
 * @var string $giftcard_number
 * @var float $giftcard_value
 * @var string $controller_name
 * @var array $config
 */
?>

<?= form_open("giftcards/save/$giftcard_id", ['id' => 'giftcard_form']) ?>

    <ul id="error_message_box" class="alert alert-warning d-none"></ul>

    <label for="person_name" class="form-label"><?= lang('Giftcards.person_id'); ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="person_name-icon"><i class="bi bi-person"></i></span>
        <input type="hidden" name="person_id" value="<?= (string)$selected_person_id ?>">
        <input type="text" class="form-control" name="person_name" id="person_name" aria-describedby="person_name-icon" value="<?= $selected_person_name ?>">
    </div>

    <?php
    $class = '';
    $label = '';
    if ($config['giftcard_number'] == 'series') {
        $class = 'required';
        $label = '<sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup>';
    }
    ?>
    <label for="giftcard_number" class="form-label"><?= lang('Giftcards.giftcard_number'); ?><?= $label ?></label>
    <div class="input-group mb-3">
        <span class="input-group-text" id="giftcard_number-icon"><i class="bi bi-gift"></i></span>
        <input type="text" class="form-control" name="giftcard_number" id="giftcard_number" aria-describedby="giftcard_number-icon" value="<?= $giftcard_number ?>" <?= $class ?>>
    </div>

    <label for="giftcard_amount" class="form-label"><?= lang('Giftcards.card_value') ?><sup><span class="badge text-primary"><i class="bi bi-asterisk"></i></span></sup></label>
    <div class="input-group mb-3">
        <?php if (!is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="giftcard_amount-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
        <input type="number" class="form-control" name="giftcard_amount" id="giftcard_amount" aria-describedby="giftcard_amount-icon" value="<?= number_format((float)$giftcard_value, 2, '.', '') ?>" required>
        <?php if (is_right_side_currency_symbol()): ?>
            <span class="input-group-text" id="giftcard_amount-icon"><?= esc($config['currency_symbol']) ?></span>
        <?php endif; ?>
    </div>

<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        $("input[name='person_name']").change(function() {
            !$(this).val() && $(this).val('');
        });

        var fill_value = function(event, ui) {
            event.preventDefault();
            $(this).val((ui.item ? ui.item.label : ""));
            $("input[name='person_id']").val(ui.item.value);
            $("input[name='person_name']").val(ui.item.label);
        };

        $('#person_name').autocomplete({
            source: "<?= esc("customers/suggest") ?>",
            minChars: 0,
            delay: 15,
            change: fill_value,
            cacheLength: 1,
            appendTo: '.modal-content',
            select: fill_value,
            focus: fill_value
        });

        $('#giftcard_form').validate($.extend({
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response) {
                        dialog_support.hide();
                        table_support.handle_submit("<?= esc($controller_name) ?>", response);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        table_support.handle_submit("<?= esc($controller_name) ?>", {
                            message: errorThrown
                        });
                    },
                    dataType: 'json'
                });
            },

            errorLabelContainer: '#error_message_box',

            rules: {
                <?php if ($config['giftcard_number'] == 'series') { ?>
                    giftcard_number: {
                        required: true,
                        number: true,
                        remote: {
                            url: "<?= esc("$controller_name/checkNumberGiftcard") ?>",
                            type: 'POST',
                            data: {
                                'giftcard_number': function() { return $('#giftcard_number').val() },
                                'giftcard_id': '<?= esc($giftcard_id) ?>'
                            }
                        }
                    },
                <?php } ?>
                giftcard_amount: {
                    required: true,
                    remote: "<?= esc("$controller_name/checkNumeric") ?>"
                }
            },

            messages: {
                <?php if ($config['giftcard_number'] == 'series') { ?>
                    giftcard_number: {
                        required: "<?= lang('Giftcards.number_required') ?>",
                        number: "<?= lang('Giftcards.number') ?>",
                        remote: "<?= lang('Giftcards.number_required') ?>"
                    },
                <?php } ?>
                giftcard_amount: {
                    required: "<?= lang('Giftcards.value_required') ?>",
                    remote: "<?= lang('Giftcards.value') ?>"
                }
            }
        }, form_support.error));
    });
</script>
