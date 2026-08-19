<?php
/**
 * @var array $stock_locations
 */
?>

<?= form_open('config/saveLocations/', ['id' => 'location_config_form', 'class' => 'form-horizontal']) ?>
    <div id="config_wrapper">
        <fieldset id="config_info">

            <div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
            <ul id="stock_error_message_box" class="error_message_box"></ul>

            <div id="stock_locations">
                <?= view('partial/stock_locations', ['stock_locations' => $stock_locations]) ?>
            </div>
            <?= form_hidden('stock_location_order', '') ?>

            <?= form_submit([
                'name'  => 'submit_stock',
                'id'    => 'submit_stock',
                'value' => lang('Common.submit'),
                'class' => 'btn btn-primary btn-sm pull-right'
            ]) ?>

        </fieldset>
    </div>
<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit handling
    $(document).ready(function() {
        let location_count = <?= sizeof($stock_locations) ?>;
        let new_location_counter = 0;

        const hide_show_remove = function() {
            if ($('#stock_locations .stock_location_row').length > 1) {
                $('.remove_stock_location').show();
            } else {
                $('.remove_stock_location').hide();
            }
        };

        const ensure_default_selected = function() {
            if ($('#stock_locations input.stock_location_default:checked').length === 0) {
                $('#stock_locations .stock_location_row').filter(function() {
                    return String($(this).data('location-id')).indexOf('new-') !== 0;
                }).first().find('input.stock_location_default').prop('checked', true);
            }
        };

        const add_stock_location = function() {
            const block = $(this).parent().clone(true);
            const new_block = block.insertAfter($(this).parent());
            const new_block_id = 'stock_location[]';
            $(new_block).attr('data-location-id', 'new-' + ++new_location_counter).find('input.stock_location_default').prop('checked', false).prop('disabled', true).val('');
            $(new_block).find('label').html("<?= lang('Config.stock_location') ?> " + ++location_count).attr('for', new_block_id).attr('class', 'control-label col-xs-2');
            $(new_block).find('input.stock_location').attr('id', new_block_id).removeAttr('disabled').attr('name', new_block_id).attr('class', 'stock_location valid_chars form-control input-sm required').val('');
            hide_show_remove();
            ensure_default_selected();
            update_sort_order_field();
        };

        const remove_stock_location = function() {
            $(this).parent().remove();
            hide_show_remove();
            ensure_default_selected();
            update_sort_order_field();
        };

        const init_add_remove_locations = function() {
            $('.add_stock_location').click(add_stock_location);
            $('.remove_stock_location').click(remove_stock_location);
            hide_show_remove();
        };
        init_add_remove_locations();

        const update_sort_order_field = function() {
            const orderedIds = $('#stock_locations .stock_location_row').map(function() {
                return $(this).data('location-id');
            }).get();
            $("input[name='stock_location_order']").val(orderedIds.join(','));
        };

        const init_sortable_locations = function() {
            const $stock_locations = $('#stock_locations');
            if ($stock_locations.hasClass('ui-sortable')) {
                $stock_locations.sortable('destroy');
            }
            $stock_locations.sortable({
                handle: '.drag_handle',
                items: '.stock_location_row',
                update: update_sort_order_field
            });
            update_sort_order_field();
        };
        init_sortable_locations();

        const duplicate_found = false;
        // Run validator once for all fields
        $.validator.addMethod('stock_location', function(value, element) {
            let value_count = 0;
            $("input[name*='stock_location']").each(function() {
                value_count = $(this).val() == value ? value_count + 1 : value_count;
            });
            return value_count < 2;
        }, "<?= lang('Config.stock_location_duplicate') ?>");

        $.validator.addMethod('valid_chars', function(value, element) {
            return value.indexOf('_') === -1;
        }, "<?= lang('Config.stock_location_invalid_chars') ?>");

        $('#location_config_form').validate($.extend(form_support.handler, {
            submitHandler: function(form) {
                update_sort_order_field();
                $(form).ajaxSubmit({
                    success: function(response) {
                        $.notify({
                            message: response.message
                        }, {
                            type: response.success ? 'success' : 'danger'
                        });
                        $("#stock_locations").load('<?= "config/stockLocations" ?>', function() {
                            init_add_remove_locations();
                            init_sortable_locations();
                        });
                    },
                    dataType: 'json'
                });
            },

            errorLabelContainer: "#stock_error_message_box",

            rules: {
                <?php foreach ($stock_locations as $location => $locationData) { ?>
                    "<?= 'stock_location[' . esc($locationData['location_id'], 'js') . ']' ?>": {
                        required: true,
                        stock_location: true,
                        valid_chars: true
                    },
                <?php } ?>
            },

            messages: {
                <?php foreach ($stock_locations as $location => $locationData) { ?>
                    "<?= 'stock_location[' . esc($locationData['location_id'], 'js') . ']' ?>": "<?= lang('Config.stock_location_required') ?>",
                <?php } ?>
            }
        }));
    });
</script>
