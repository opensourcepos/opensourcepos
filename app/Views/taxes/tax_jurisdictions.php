<?php
/**
 * @var array $tax_jurisdictions
 * @var string $tax_type_options
 */
?>

<?= form_open('taxes/save_tax_jurisdictions/', ['id' => 'tax_jurisdictions_form', 'class' => 'form-horizontal']) ?>
    <div id="config_wrapper">
        <fieldset id="config_info">

            <div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
            <ul id="tax_jurisdictions_error_message_box" class="error_message_box"></ul>

            <div id="tax_jurisdictions">
                <?= view('partial/tax_jurisdictions') ?>
            </div>

            <?= form_submit([
                'name'  => 'submit_tax_jurisdictions',
                'id'    => 'submit_tax_jurisdictions',
                'value' => lang('Common.submit'),
                'class' => 'btn btn-primary btn-sm pull-right'
            ]) ?>

        </fieldset>
    </div>
<?= form_close() ?>

<script type="text/javascript">
    // Validation and submit Handling
    $(document).ready(function() {
        let tax_jurisdictions_count = <?= sizeof($tax_jurisdictions) ?>;
        if (tax_jurisdictions_count == 0) {
            tax_jurisdictions_count = 1;
        }
        const tax_type_options = '<?= esc($tax_type_options, 'js') ?>';

        const hide_show_remove_tax_jurisdiction = function() {
            if ($("input[name*='tax_jurisdiction']:enabled").length > 1) {
                $(".remove_tax_jurisdiction").show();
            } else {
                $(".remove_tax_jurisdiction").hide();
            }
        };

        const add_tax_jurisdiction = function() {
            let id = $(this).parent().find('input').attr('id');
            id = id.replace(/.*?_(\d+)$/g, "$1");

            const previous_jurisdiction_name_id = 'jurisdiction_name_' + id;
            const block = $(this).parent().clone(true);
            const new_block = block.insertAfter($(this).parent());
            ++tax_jurisdictions_count;
            const new_jurisdiction_name_id = 'jurisdiction_name_' + tax_jurisdictions_count;

            $(new_block).find('label').html("<?= lang('Taxes.tax_jurisdiction') ?> " + tax_jurisdictions_count).attr('for', new_jurisdiction_name_id).attr('class', 'control-label col-xs-2');
            $(new_block).find("input[name='jurisdiction_name[]']").attr('id', new_jurisdiction_name_id).removeAttr('disabled').attr('class', 'form-control required input-sm').val('');
            $(new_block).find("input[name='tax_group[]']").removeAttr('disabled').attr('class', 'form-control required input-sm').val('');
            $(new_block).find("select[name='tax_type[]']").removeAttr('disabled').attr('class', 'form-control required input-sm').val('');
            $(new_block).find("input[name='reporting_authority[]']").removeAttr('disabled').attr('class', 'form-control input-sm').val('');
            $(new_block).find("input[name='tax_group_sequence[]']").removeAttr('disabled').attr('class', 'form-control input-sm').val('');
            $(new_block).find("input[name='cascade_sequence[]']").removeAttr('disabled').attr('class', 'form-control input-sm').val('');
            $(new_block).find("input[name='jurisdiction_id[]']").val('-1');
            hide_show_remove_tax_jurisdiction();
        };

        const remove_tax_jurisdiction = function() {
            $(this).parent().remove();
            hide_show_remove_tax_jurisdiction();
        };

        const init_add_remove_tax_jurisdiction = function() {
            $('.add_tax_jurisdiction').click(add_tax_jurisdiction);
            $('.remove_tax_jurisdiction').click(remove_tax_jurisdiction);
            hide_show_remove_tax_jurisdiction();
        };
        init_add_remove_tax_jurisdiction();

        // Run validator once for all fields
        $.validator.addMethod('check4TaxJurisdictionDups', function(value, element) {
            let value_count = 0;
            $("input[name='jurisdiction_name[]']").each(function() {
                value_count = $(this).val() == value ? value_count + 1 : value_count;
            });
            return value_count <= 1;

        }, "<?= lang('Taxes.tax_jurisdiction_duplicate') ?>");

        $.validator.addMethod('validateTaxJurisdictionCharacters', function(value, element) {
            return (value.indexOf('_') == -1);

        }, "<?= lang('Taxes.tax_jurisdiction_invalid_chars') ?>");

        $.validator.addMethod('requireTaxJurisdiction', function(value, element) {
            return value.trim() != '';

        }, "<?= lang('Taxes.tax_jurisdiction_required') ?>");

        $('#tax_jurisdictions_form').validate($.extend(form_support.handler, {
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response) {
                        $.notify({
                            message: response.message
                        }, {
                            type: response.success ? 'success' : 'danger'
                        });
                        $("#tax_jurisdictions").load('<?= esc("taxes/ajax_tax_jurisdictions") ?>', init_add_remove_tax_jurisdiction);
                    },
                    dataType: 'json'
                });
            },
            invalidHandler: function(event, validator) {
                $.notify("<?= lang('Common.correct_errors') ?>");
            },
            errorLabelContainer: "#tax_jurisdiction_error_message_box"
        }));

        <?php
        $i = 0;
        foreach ($tax_jurisdictions as $tax_jurisdiction => $tax_jurisdiction_data) {
        ?>
            $('<?= '#jurisdiction_name_' . ++$i ?>').rules("add", {
                requireTaxJurisdiction: true,
                check4TaxJurisdictionDups: true,
                validateTaxJurisdictionCharacters: true
            });
        <?php } ?>
    });
</script>
