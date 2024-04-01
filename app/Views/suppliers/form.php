<?php
/**
 * @var string $controller_name
 * @var object $person_info
 * @var array $categories
 */
?>
<div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?= form_open("$controller_name/save/$person_info->person_id", ['id' => 'supplier_form', 'class' => 'form-horizontal']) ?>
	<fieldset id="supplier_basic_info">
		<div class="form-group form-group-sm">
			<?= form_label(lang('Suppliers.company_name'), 'company_name', ['class' => 'required control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?= form_input([
					'name' => 'company_name',
					'id' => 'company_name_input',
					'class' => 'form-control input-sm',
					'value' => html_entity_decode($person_info->company_name)
					])
				?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Suppliers.category'), 'category', ['class' => 'required control-label col-xs-3']) ?>
			<div class='col-xs-6'>
				<?= form_dropdown('category', $categories, $person_info->category, ['class' => 'form-control', 'id' => 'category']) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Suppliers.agency_name'), 'agency_name', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?= form_input ([
					'name' => 'agency_name',
					'id' => 'agency_name_input',
					'class' => 'form-control input-sm',
					'value' => $person_info->agency_name
					])
				?>
			</div>
		</div>

		<?= view('people/form_basic_info') ?>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Suppliers.account_number'), 'account_number', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?= form_input ([
					'name' => 'account_number',
					'id' => 'account_number',
					'class' => 'form-control input-sm',
					'value' => $person_info->account_number
					])
				?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Suppliers.tax_id'), 'tax_id', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?= form_input ([
						'name' => 'tax_id',
						'id' => 'tax_id',
						'class' => 'form-control input-sm',
						'value' => $person_info->tax_id
					])
				?>
			</div>
		</div>
	</fieldset>
<?= form_close() ?>

<script type="application/javascript">
//validation and submit handling
$(document).ready(function()
{
	$('#supplier_form').validate($.extend({
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				success: function(response)
				{
					dialog_support.hide();
					table_support.handle_submit("<?= esc($controller_name) ?>", response);
				},
				dataType: 'json'
			});
		},

		errorLabelContainer: '#error_message_box',

		rules:
		{
			company_name: 'required',
			first_name: 'required',
			last_name: 'required',
			email: 'email'
   		},

		messages:
		{
			company_name: "<?= lang('Suppliers.company_name_required') ?>",
			first_name: "<?= lang('Common.first_name_required') ?>",
			last_name: "<?= lang('Common.last_name_required') ?>",
			email: "<?= lang('Common.email_invalid_format') ?>"
		}
	}, form_support.error));
});
</script>
