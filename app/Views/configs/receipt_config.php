<?php
/**
 * @var array $config
 */
?>
<?= form_open('config/saveReceipt/', ['id' => 'receipt_config_form', 'class' => 'form-horizontal']) ?>
	<div id="config_wrapper">
		<fieldset id="config_info">
			<div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>
			<ul id="receipt_error_message_box" class="error_message_box"></ul>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.receipt_template'), 'receipt_template', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?= form_dropdown(
						'receipt_template',
						[
							'receipt_default' => lang('Config.receipt_default'),
							'receipt_short' => lang('Config.receipt_short')
						],
						$config['receipt_template'],
						"class='form-control input-sm'"
					) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.receipt_font_size'), 'receipt_font_size', ['class' => 'control-label col-xs-2 required']) ?>
				<div class='col-xs-2'>
					<div class="input-group">
						<?= form_input ([
							'type' => 'number',
							'min' => '0',
							'max' => '20',
							'name' => 'receipt_font_size',
							'id' => 'receipt_font_size',
							'class' => 'form-control input-sm required',
							'value' => $config['receipt_font_size']
						]) ?>
						<span class="input-group-addon input-sm">px</span>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.print_delay_autoreturn'), 'print_delay_autoreturn', ['class' => 'control-label col-xs-2 required']) ?>
				<div class='col-xs-2'>
					<div class="input-group">
						<?= form_input ([
							'type' => 'number',
							'min' => '0',
							'max' => '30',
							'name' => 'print_delay_autoreturn',
							'id' => 'print_delay_autoreturn',
							'class' => 'form-control input-sm required',
							'value' => $config['print_delay_autoreturn']
						]) ?>
						<span class="input-group-addon input-sm">s</span>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.email_receipt_check_behaviour'), 'email_receipt_check_behaviour', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-8'>
					<label class="radio-inline">
						<?= form_radio ([
							'name' => 'email_receipt_check_behaviour',
							'value' => 'always',
							'checked' => $config['email_receipt_check_behaviour'] == 'always'
						]) ?>
						<?= lang('Config.email_receipt_check_behaviour_always') ?>
					</label>
					<label class="radio-inline">
						<?= form_radio ([
							'name' => 'email_receipt_check_behaviour',
							'value' => 'never',
							'checked' => $config['email_receipt_check_behaviour'] == 'never'
						]) ?>
						<?= lang('Config.email_receipt_check_behaviour_never') ?>
					</label>
					<label class="radio-inline">
						<?= form_radio ([
							'name' => 'email_receipt_check_behaviour',
							'value' => 'last',
							'checked' => $config['email_receipt_check_behaviour'] == 'last'
						]) ?>
						<?= lang('Config.email_receipt_check_behaviour_last') ?>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.print_receipt_check_behaviour'), 'print_receipt_check_behaviour', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-8'>
					<label class="radio-inline">
						<?= form_radio ([
							'name' => 'print_receipt_check_behaviour',
							'value' => 'always',
							'checked' => $config['print_receipt_check_behaviour'] == 'always'
						]) ?>
						<?= lang('Config.print_receipt_check_behaviour_always') ?>
					</label>
					<label class="radio-inline">
						<?= form_radio ([
							'name' => 'print_receipt_check_behaviour',
							'value' => 'never',
							'checked' => $config['print_receipt_check_behaviour'] == 'never'
						]) ?>
						<?= lang('Config.print_receipt_check_behaviour_never') ?>
					</label>
					<label class="radio-inline">
						<?= form_radio ([
							'name' => 'print_receipt_check_behaviour',
							'value' => 'last',
							'checked' => $config['print_receipt_check_behaviour'] == 'last'
						]) ?>
						<?= lang('Config.print_receipt_check_behaviour_last') ?>
					</label>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.receipt_show_company_name'), 'receipt_show_company_name', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_checkbox ([
						'name' => 'receipt_show_company_name',
						'value' => 'receipt_show_company_name',
						'id' => 'receipt_show_company_name',
						'checked' => $config['receipt_show_company_name'] == 1
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.receipt_show_taxes'), 'receipt_show_taxes', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_checkbox ([
						'name' => 'receipt_show_taxes',
						'value' => 'receipt_show_taxes',
						'id' => 'receipt_show_taxes',
						'checked' => $config['receipt_show_taxes'] == 1
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.receipt_show_tax_ind'), 'receipt_show_tax_ind', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_checkbox ([
						'name' => 'receipt_show_tax_ind',
						'value' => 'receipt_show_tax_ind',
						'id' => 'receipt_show_tax_ind',
						'checked' => $config['receipt_show_tax_ind'] == 1
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.receipt_show_total_discount'), 'receipt_show_total_discount', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_checkbox ([
						'name' => 'receipt_show_total_discount',
						'value' => 'receipt_show_total_discount',
						'id' => 'receipt_show_total_discount',
						'checked' => $config['receipt_show_total_discount'] == 1
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.receipt_show_description'), 'receipt_show_description', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_checkbox ([
						'name' => 'receipt_show_description',
						'value' => 'receipt_show_description',
						'id' => 'receipt_show_description',
						'checked' => $config['receipt_show_description'] == 1
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.receipt_show_serialnumber'), 'receipt_show_serialnumber', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_checkbox ([
						'name' => 'receipt_show_serialnumber',
						'value' => 'receipt_show_serialnumber',
						'id' => 'receipt_show_serialnumber',
						'checked' => $config['receipt_show_serialnumber'] == 1
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.print_silently'), 'print_silently', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_checkbox ([
						'name' => 'print_silently',
						'id' => 'print_silently',
						'value' => 'print_silently',
						'checked' => $config['print_silently'] == 1
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.print_header'), 'print_header', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_checkbox ([
						'name' => 'print_header',
						'id' => 'print_header',
						'value' => 'print_header',
						'checked' => $config['print_header'] == 1
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.print_footer'), 'print_footer', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-1'>
					<?= form_checkbox ([
						'name' => 'print_footer',
						'id' => 'print_footer',
						'value' => 'print_footer',
						'checked' => $config['print_footer'] == 1
					]) ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.receipt_printer'), 'config_receipt_printer', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?= form_dropdown('receipt_printer', [], ' ', 'id="receipt_printer" class="form-control"') ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.invoice_printer'), 'config_invoice_printer', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?= form_dropdown('invoice_printer', [], ' ', 'id="invoice_printer" class="form-control"') ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.takings_printer'), 'config_takings_printer', ['class' => 'control-label col-xs-2']) ?>
				<div class='col-xs-2'>
					<?= form_dropdown('takings_printer', [], ' ', 'id="takings_printer" class="form-control"') ?>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.print_top_margin'), 'print_top_margin', ['class' => 'control-label col-xs-2 required']) ?>
				<div class='col-xs-2'>
					<div class="input-group">
						<?= form_input ([
							'type' => 'number',
							'min' => '0',
							'max' => '20',
							'name' => 'print_top_margin',
							'id' => 'print_top_margin',
							'class' => 'form-control input-sm required',
							'value' => $config['print_top_margin']
						]) ?>
						<span class="input-group-addon input-sm">px</span>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.print_left_margin'), 'print_left_margin', ['class' => 'control-label col-xs-2 required']) ?>
				<div class='col-xs-2'>
					<div class="input-group">
						<?= form_input ([
							'type' => 'number',
							'min' => '0',
							'max' => '20',
							'name' => 'print_left_margin',
							'id' => 'print_left_margin',
							'class' => 'form-control input-sm required',
							'value' => $config['print_left_margin']
						]) ?>
						<span class="input-group-addon input-sm">px</span>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.print_bottom_margin'), 'print_bottom_margin', ['class' => 'control-label col-xs-2 required']) ?>
				<div class='col-xs-2'>
					<div class="input-group">
						<?= form_input ([
							'type' => 'number',
							'min' => '0',
							'max' => '20',
							'name' => 'print_bottom_margin',
							'id' => 'print_bottom_margin',
							'class' => 'form-control input-sm required',
							'value' => $config['print_bottom_margin']
						]) ?>
						<span class="input-group-addon input-sm">px</span>
					</div>
				</div>
			</div>

			<div class="form-group form-group-sm">
				<?= form_label(lang('Config.print_right_margin'), 'print_right_margin', ['class' => 'control-label col-xs-2 required']) ?>
				<div class='col-xs-2'>
					<div class="input-group">
						<?= form_input ([
							'type' => 'number',
							'min' => '0',
							'max' => '20',
							'name' => 'print_right_margin',
							'id' => 'print_right_margin',
							'class' => 'form-control input-sm required',
							'value' => $config['print_right_margin']
						]) ?>
						<span class="input-group-addon input-sm">px</span>
					</div>
				</div>
			</div>

			<?= form_submit ([
				'name' => 'submit_receipt',
				'id' => 'submit_receipt',
				'value' => lang('Common.submit'),
				'class' => 'btn btn-primary btn-sm pull-right'
			]) ?>
		</fieldset>
	</div>
<?= form_close() ?>

<script type="application/javascript">
//validation and submit handling
$(document).ready(function()
{
	if (window.localStorage && window.jsPrintSetup)
	{
		var printers = (jsPrintSetup.getPrintersList() && jsPrintSetup.getPrintersList().split(',')) || [];
		$('#receipt_printer, #invoice_printer, #takings_printer').each(function()
		{
			var $this = $(this)
			$(printers).each(function(key, value)
			{
				 $this.append($('<option>', { value : value }).text(value));
			});
			$("option[value='" + localStorage[$(this).attr('id')] + "']", this).prop('selected', true);
			$(this).change(function()
			{
				localStorage[$(this).attr('id')] = $(this).val();
			});
		});
	}
	else
	{
		$("input[id*='margin'], #print_footer, #print_header, #receipt_printer, #invoice_printer, #takings_printer, #print_silently").prop('disabled', true);
		$("#receipt_printer, #invoice_printer, #takings_printer").each(function()
		{
			$(this).append($('<option>', {value : 'na'}).text('N/A'));
		});
	}

	var dialog_confirmed = window.jsPrintSetup;

	$('#receipt_config_form').validate($.extend(form_support.handler, {
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				beforeSerialize: function(arr, $form, options) {
					return ( dialog_confirmed || confirm('<?= lang('Config.jsprintsetup_required') ?>') );
				},
				success: function(response) {
					$.notify( { message: response.message }, { type: response.success ? 'success' : 'danger'} )
				},
				dataType:'json'
			});
		},

		errorLabelContainer: "#receipt_error_message_box",

		rules:
		{
			print_top_margin:
			{
				required:true,
				number:true
			},
			print_left_margin:
			{
				required:true,
				number:true
			},
			print_bottom_margin:
			{
				required:true,
				number:true
			},
			print_right_margin:
			{
				required:true,
				number:true
			},
			receipt_font_size:
			{
				required:true,
				number:true
			},
			print_delay_autoreturn:
			{
				required:true,
				number:true
			}
		},

		messages:
		{
			print_top_margin:
			{
				required:"<?= lang('Config.print_top_margin_required') ?>",
				number:"<?= lang('Config.print_top_margin_number') ?>"
			},
			print_left_margin:
			{
				required:"<?= lang('Config.print_left_margin_required') ?>",
				number:"<?= lang('Config.print_left_margin_number') ?>"
			},
			print_bottom_margin:
			{
				required:"<?= lang('Config.print_bottom_margin_required') ?>",
				number:"<?= lang('Config.print_bottom_margin_number') ?>"
			},
			print_right_margin:
			{
				required:"<?= lang('Config.print_right_margin_required') ?>",
				number:"<?= lang('Config.print_right_margin_number') ?>"
			},
			receipt_font_size:
			{
				required:"<?= lang('Config.receipt_font_size_required') ?>",
				number:"<?= lang('Config.receipt_font_size_number') ?>"
			},
			print_delay_autoreturn:
			{
				required:"<?= lang('Config.print_delay_autoreturn_required') ?>",
				number:"<?= lang('Config.print_delay_autoreturn_number') ?>"
			}
		}
	}));
});
</script>
