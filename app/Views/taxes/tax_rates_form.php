<?php
/**
 * @var int $tax_rate_id
 * @var array $tax_code_options
 * @var array $rate_tax_code_id
 * @var array $tax_category_options
 * @var array $rate_tax_category_id
 * @var array $tax_jurisdiction_options
 * @var array $rate_jurisdiction_id
 * @var float $tax_rate
 * @var array $rounding_options
 * @var array $tax_rounding_code
 */
?>
<ul id="error_message_box" class="error_message_box"></ul>

<?= form_open(esc("taxes/save/$tax_rate_id", 'url'), ['id' => 'tax_code_form', 'class' => 'form-horizontal']) ?>
	<fieldset id="tax_rate_info">
		<div class="form-group form-group-sm">
			<?= form_label(lang('Taxes.tax_code'), 'rate_tax_code_id', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-5'>
				<?= form_dropdown('rate_tax_code_id',$tax_code_options, $rate_tax_code_id, ['class' => 'form-control input-sm']) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Taxes.tax_category'), 'rate_tax_category_id', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-5'>
				<?= form_dropdown('rate_tax_category_id', $tax_category_options, $rate_tax_category_id, ['class' => 'form-control input-sm']) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Taxes.tax_jurisdiction'), 'rate_jurisdiction_id', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-5'>
				<?= form_dropdown('rate_jurisdiction_id', $tax_jurisdiction_options, $rate_jurisdiction_id, ['class' => 'form-control input-sm']) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Taxes.tax_rate'), 'tax_rate', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-5 input-group' style='padding-left:15px;' >
				<?= form_input ([
						'name' => 'tax_rate',
						'id' => 'tax_rate',
						'class' => 'form-control input-sm text-uppercase',
						'value' => esc($tax_rate)
					])
				?>
				<span class="input-group-addon input-sm">%</span>
			</div>

		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Taxes.tax_rounding'), 'tax_rounding_code', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-5'>
				<?= form_dropdown('tax_rounding_code', $rounding_options, $tax_rounding_code, ['class' => 'form-control input-sm']) ?>
			</div>
		</div>
	</fieldset>

<?= form_close() ?>

<script type="text/javascript">
    //validation and submit handling
    $(document).ready(function() {

        $('#tax_code_form').validate($.extend({
            submitHandler: function (form) {
                $(form).ajaxSubmit({
                    success: function (response) {
                        dialog_support.hide();
                        table_support.handle_submit('<?='taxes' ?>', response);
                    },
                    dataType: 'json'
                });
            },
            rules: {
            },
            messages: {
            }
        }, form_support.error));


    });

    function delete_tax_rate_row(link) {
        $(link).parent().parent().remove();
        return false;
    }

</script>
