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

		var hide_show_remove = function() {
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

			var previous_tax_category_id_id = 'tax_category_id_' + id;
			var previous_tax_category_id = 'tax_category_' + id;
			var previous_default_tax_rate_id = 'default_tax_rate_' + id;
			var previous_tax_group_sequence_id = 'tax_group_sequence_' + id;
			var block = $(this).parent().clone(true);
			var new_block = block.insertAfter($(this).parent());
			var new_tax_category_id_id = 'tax_category_id_' + ++tax_categories_count;
			var new_tax_category_id = 'tax_category_' + tax_categories_count;
			var new_default_tax_rate_id = 'default_tax_rate_' + tax_categories_count;
			var new_tax_group_sequence_id = 'tax_group_sequence_' + tax_categories_count;

			$(new_block).find('label').html("<?php echo $this->lang->line('taxes_tax_category'); ?> " + tax_categories_count).attr('for', new_tax_category_id).attr('class', 'control-label col-xs-2');
			$(new_block).find("input[name='"+previous_tax_category_id_id+"']").attr('name', new_tax_category_id_id).val('-1');
			$(new_block).find("input[id='"+previous_tax_category_id+"']").attr('id', new_tax_category_id).removeAttr('disabled').attr('name', new_tax_category_id).attr('class', 'form-control input-sm').val('');
			$(new_block).find("input[id='"+previous_default_tax_rate_id+"']").attr('id', new_default_tax_rate_id).removeAttr('disabled').attr('name', new_default_tax_rate_id).attr('class', 'form-control input-sm').val('');
			$(new_block).find("input[id='"+previous_tax_group_sequence_id+"']").attr('id', new_tax_group_sequence_id).removeAttr('disabled').attr('name', new_tax_group_sequence_id).attr('class', 'form-control input-sm').val('');
			hide_show_remove();
		};

		var remove_tax_category = function() {
			$(this).parent().remove();
			hide_show_remove();
		};

		var init_add_remove_tax_categories = function() {
			$('.add_tax_category').click(add_tax_category);
			$('.remove_tax_category').click(remove_tax_category);
			hide_show_remove();
		};
		init_add_remove_tax_categories();

		var duplicate_found = false;
		// run validator once for all fields
		$.validator.addMethod('tax_category' , function(value, element) {
			var value_count = 0;
			$("input[name*='tax_category']").each(function() {
				value_count = $(this).val() == value ? value_count + 1 : value_count;
			});
			return value_count < 2;
		}, "<?php echo $this->lang->line('taxes_tax_category_duplicate'); ?>");

		$.validator.addMethod('valid_chars', function(value, element) {
			return value.indexOf('_') === -1;
		}, "<?php echo $this->lang->line('taxes_tax_category_invalid_chars'); ?>");

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

			errorLabelContainer: "#tax_category_error_message_box",

			rules:
			{
			<?php
			$i = 0;

			foreach($tax_categories as $tax_category=>$tax_category_data)
			{
			?>
			<?php echo 'tax_category_' . ++$i ?>:
			{
			required: true,
				tax_category: true,
				valid_chars: true
			},
			<?php
			}
			?>
			},

			messages:
			{
			<?php
			$i = 0;

			foreach($tax_categories as $tax_category=>$tax_category_data)
			{
			?>
			<?php echo 'tax_category_' . ++$i ?>: "<?php echo $this->lang->line('taxes_tax_category_required'); ?>",
			<?php
			}
			?>
			}
		}));
	});
</script>
