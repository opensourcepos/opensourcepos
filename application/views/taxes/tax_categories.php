<?php echo form_open('taxes/save_tax_categories/', array('id' => 'tax_categories_form', 'class' => 'form-horizontal')); ?>
<div id="config_wrapper">
	<fieldset id="config_info">
		<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
		<ul id="tax_categories_error_message_box" class="error_message_box"></ul>

		<div id="tax_categories">
			<?php $this->load->view('partial/tax_categories'); ?>
		</div>

		<?php echo form_submit(array(
			'name' => 'submit_tax_categories',
			'id' => 'submit_tax_categories',
			'value' => $this->lang->line('common_submit'),
			'class' => 'btn btn-primary btn-sm pull-right')); ?>
	</fieldset>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
	//validation and submit handling
	$(document).ready(function()
	{
		var tax_categories_count = <?php echo sizeof($tax_categories); ?>;
		if (tax_categories_count == 0) {
			tax_categories_count = 1;
		}

		var hide_show_remove_tax_category = function() {
			if ($("input[name*='tax_category']:enabled").length > 1)
			{
				$(".remove_tax_category").show();
			}
			else
			{
				$(".remove_tax_category").hide();
			}
		};

		var add_tax_category = function() {
			var id = $(this).parent().find('input').attr('id');
			id = id.replace(/.*?_(\d+)$/g, "$1");

			var previous_tax_category_id = 'tax_category_' + id;
			var block = $(this).parent().clone(true);
			var new_block = block.insertAfter($(this).parent());
			++tax_categories_count;
			var new_tax_category_id = 'tax_category_' + tax_categories_count;

			$(new_block).find('label').html("<?php echo $this->lang->line('taxes_tax_category'); ?> " + tax_categories_count).attr('for', new_tax_category_id).attr('class', 'control-label col-xs-2');
			$(new_block).find("input[name='tax_category[]']").attr('id', new_tax_category_id).removeAttr('disabled').attr('class', 'form-control input-sm required').val('');
			$(new_block).find("input[name='tax_group_sequence[]']").removeAttr('disabled').attr('class', 'form-control input-sm').val('');
			$(new_block).find("input[name='tax_category_id[]']").val('-1');
			hide_show_remove_tax_category();
		};

		var remove_tax_category = function() {
			$(this).parent().remove();
			hide_show_remove_tax_category();
		};

		var init_add_remove_tax_categories = function() {
			$('.add_tax_category').click(add_tax_category);
			$('.remove_tax_category').click(remove_tax_category);
			hide_show_remove_tax_category();
		};
		init_add_remove_tax_categories();

		var duplicate_found = false;

		// run validator once for all fields
		$.validator.addMethod("check4TaxCategoryDups" , function(value, element) {
			var value_count = 0;
			$('input[name="tax_category[]"]').each(function() {
				value_count = $(this).val() == value ? value_count + 1 : value_count;
			});
			if (value_count > 1) {
				return false;
			}
			return true;
		}, "<?php echo $this->lang->line('taxes_tax_category_duplicate'); ?>");

		$.validator.addMethod('validateTaxCategoryCharacters', function(value, element) {
			if ((value.indexOf('_') != -1)) {
				return false;
			}
			return true;
		}, "<?php echo $this->lang->line('taxes_tax_category_invalid_chars'); ?>");

		$.validator.addMethod('requireTaxCategory', function(value, element) {
			if (value .trim() == '') {
				return false;
			}
			return true;
		}, "<?php echo $this->lang->line('taxes_tax_category_required'); ?>");

		$('#tax_categories_form').validate($.extend(form_support.handler, {
			submitHandler: function(form) {
				$(form).ajaxSubmit({
					success: function(response)	{
						$.notify({ message: response.message }, { type: response.success ? 'success' : 'danger'});
						$("#tax_categories").load('<?php echo site_url("taxes/ajax_tax_categories"); ?>', init_add_remove_tax_categories);
					},
					dataType: 'json'
				});
			},
			invalidHandler: function(event, validator) {
				$.notify("<?php echo $this->lang->line('common_correct_errors'); ?>");
			},
			errorLabelContainer: "#tax_category_error_message_box"
		}));

		<?php
		$i = 0;
		foreach($tax_categories as $tax_category=>$tax_category_data)
		{
		?>
		$('<?php echo '#tax_category_' . ++$i ?>').rules( "add", {
			requireTaxCategory: true,
			check4TaxCategoryDups: true,
			validateTaxCategoryCharacters: true
		});
		<?php
		}
		?>

	});
</script>
