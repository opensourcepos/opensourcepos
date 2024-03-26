<?php
/**
 * @var object $category_info
 * @var string $controller_name
 */
?>
<div id="required_fields_message"><?= lang('Common.fields_required_message') ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?= form_open("expenses_categories/save/$category_info->expense_category_id", ['id' => 'expense_category_edit_form', 'class' => 'form-horizontal']) ?>
	<fieldset id="expenses_categories">
		<div class="form-group form-group-sm">
			<?= form_label(lang('Expenses_categories.name'), 'category_name', ['class' => 'required control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?= form_input ([
					'name' => 'category_name',
					'id' => 'category_name',
					'class' => 'form-control input-sm',
					'value' => $category_info->category_name
				]) ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?= form_label(lang('Expenses_categories.description'), 'category_description', ['class' => 'control-label col-xs-3']) ?>
			<div class='col-xs-8'>
				<?= form_textarea ([
					'name' => 'category_description',
					'id' => 'category_description',
					'class' => 'form-control input-sm',
					'value' => $category_info->category_description
				]) ?>
			</div>
		</div>

	</fieldset>
<?= form_close() ?>

<script type='text/javascript'>
//validation and submit handling
$(document).ready(function()
{
	$('#expense_category_edit_form').validate($.extend({
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
			category_name: 'required'
		},

		messages:
		{
			category_name: "<?= lang('Expenses_categories.category_name_required') ?>"
		}
	}, form_support.error));
});
</script>
