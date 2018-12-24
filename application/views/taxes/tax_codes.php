<?php echo form_open('taxes/save_tax_codes/', array('id' => 'tax_codes_form', 'class' => 'form-horizontal')); ?>
<div id="config_wrapper">
	<fieldset id="config_info">
		<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
		<ul id="tax_codes_error_message_box" class="error_message_box"></ul>

		<div id="tax_codes">
			<?php $this->load->view('partial/tax_codes', array('tax_codes' => $tax_codes)); ?>
		</div>

		<?php echo form_submit(array(
			'name' => 'submit_tax_codes',
			'id' => 'submit_tax_codes',
			'value' => $this->lang->line('common_submit'),
			'class' => 'btn btn-primary btn-sm pull-right')); ?>
	</fieldset>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
	//validation and submit handling
	$(document).ready(function()
	{
		var tax_code_count = <?php echo sizeof($tax_codes); ?>;
		if (tax_code_count == 0) {
			tax_code_count = 1;
		}

		var hide_show_remove_tax_code = function() {
			if ($("input[name*='tax_code']:enabled").length > 1)
			{
				$(".remove_tax_code").show();
			}
			else
			{
				$(".remove_tax_code").hide();
			}
		};

		var add_tax_code = function() {
			var id = $(this).parent().find("input[name='tax_code[]']").attr('id');
			id = id.replace(/.*?_(\d+)$/g, "$1");
			var previous_tax_code_id = 'tax_code_' + id;
			var block = $(this).parent().clone(true);
			var new_block = block.insertAfter($(this).parent());
			++tax_code_count;
			var new_tax_code_id = 'tax_code_' + tax_code_count;

			$(new_block).find('label').html("<?php echo $this->lang->line('taxes_tax_code'); ?> " + tax_code_count).attr('for', new_tax_code_id).attr('class', 'control-label col-xs-2');
			$(new_block).find("input[name='tax_code[]']").attr('id', new_tax_code_id).removeAttr('disabled').attr('class', 'form-control text-uppercase required input-sm').val('');
			$(new_block).find("input[name='tax_code_name[]']").removeAttr('disabled').attr('class', 'form-control required input-sm').val('');
			$(new_block).find("input[name='city[]']").removeAttr('disabled').attr('class', 'form-control input-sm').val('');
			$(new_block).find("input[name='state[]']").removeAttr('disabled').attr('class', 'form-control input-sm').val('');
			$(new_block).find("input[name='tax_code_id[]']").val('-1');

			hide_show_remove_tax_code();
		};

		var remove_tax_code = function() {
			$(this).parent().remove();
			hide_show_remove_tax_code();
		};

		var init_add_remove_tax_codes = function() {
			$('.add_tax_code').click(add_tax_code);
			$('.remove_tax_code').click(remove_tax_code);
			hide_show_remove_tax_code();
		};
		init_add_remove_tax_codes();

		// run validator once for all fields
		$.validator.addMethod('check4TaxCodeDups' , function(value, element) {
			var value_count = 0;
			$("input[name='tax_code[]']").each(function() {
				value_count = $(this).val() == value ? value_count + 1 : value_count;
			});
			if (value_count > 1) {
				return false;
			}
			return true;
		}, "<?php echo $this->lang->line('taxes_tax_code_duplicate'); ?>");

		$.validator.addMethod('validateTaxCodeCharacters', function(value, element) {
			if ((value.indexOf('_') != -1)) {
				return false;
			}
			return true;
		}, "<?php echo $this->lang->line('taxes_tax_code_invalid_chars'); ?>");

		$.validator.addMethod('requireTaxCode', function(value, element) {
			if (value .trim() == '') {
				return false;
			}
			return true;
		}, "<?php echo $this->lang->line('taxes_tax_code_required'); ?>");

		$('#tax_codes_form').validate($.extend(form_support.handler, {
			submitHandler: function(form, event) {
				$(form).ajaxSubmit({
					success: function(response)	{
						$.notify({ message: response.message }, { type: response.success ? 'success' : 'danger'});
						$("#tax_codes").load('<?php echo site_url("taxes/ajax_tax_codes"); ?>', init_add_remove_tax_codes);
					},
					dataType: 'json'
				});
			},
			invalidHandler: function(event, validator) {
				$.notify("<?php echo $this->lang->line('common_correct_errors'); ?>");
			},
			errorLabelContainer: "#tax_code_error_message_box"
		}));

		<?php
		$i = 0;
		foreach($tax_codes as $tax_code=>$tax_code_data)
		{
		?>
		$('<?php echo '#tax_code_' . ++$i ?>').rules( "add", {
			requireTaxCode: true,
			check4TaxCodeDups: true,
			validateTaxCodeCharacters: true
		});
		<?php
		}
		?>

	});
</script>
