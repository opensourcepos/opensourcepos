<?php echo form_open('taxes/save_tax_jurisdictions/', array('id' => 'tax_jurisdictions_form', 'class' => 'form-horizontal')); ?>
<div id="config_wrapper">
	<fieldset id="config_info">
		<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
		<ul id="tax_jurisdictions_error_message_box" class="error_message_box"></ul>

		<div id="tax_jurisdictions">
			<?php $this->load->view('partial/tax_jurisdictions'); ?>
		</div>

		<?php echo form_submit(array(
			'name' => 'submit_tax_jurisdictions',
			'id' => 'submit_tax_jurisdictions',
			'value' => $this->lang->line('common_submit'),
			'class' => 'btn btn-primary btn-sm pull-right')); ?>
	</fieldset>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
	//validation and submit handling
	$(document).ready(function()
	{
		var tax_jurisdictions_count = <?php echo sizeof($tax_jurisdictions); ?>;
		if (tax_jurisdictions_count == 0) {
			tax_jurisdictions_count = 1;
		}
		var tax_type_options = '<?php echo $tax_type_options; ?>';

		var hide_show_remove = function() {
			if ($("input[name*='tax_jurisdiction']:enabled").length > 1)
			{
				$(".remove_tax_jurisdiction").show();
			}
			else
			{
				$(".remove_tax_jurisdictions").hide();
			}
		};

		var add_tax_jurisdiction = function() {
			var id = $(this).parent().find('input').attr('id');
			id = id.replace(/.*?_(\d+)$/g, "$1");

			var previous_jurisdiction_id = 'jurisdiction_id_' + id;
			var previous_jurisdiction_name_id = 'jurisdiction_name_' + id;
			var previous_tax_type_id = 'tax_type_' + id;
			var previous_reporting_authority_id = 'reporting_authority_' + id;
			var previous_tax_group_sequence_id = 'tax_group_sequence_' + id;
			var previous_cascade_sequence_id = 'cascade_sequence_' + id;
			var block = $(this).parent().clone(true);
			var new_block = block.insertAfter($(this).parent());
			++tax_jurisdictions_count;
			var new_jurisdiction_id = 'jurisdiction_id_' + tax_jurisdictions_count;
			var new_jurisdiction_name_id = 'jurisdiction_name_' + tax_jurisdictions_count;
			var new_tax_type_id = 'tax_type_' + tax_jurisdictions_count;
			var new_reporting_authority_id = 'reporting_authority_' + tax_jurisdictions_count;
			var new_tax_group_sequence_id = 'tax_group_sequence_' + tax_jurisdictions_count;
			var new_cascade_sequence_id = 'cascade_sequence_' + tax_jurisdictions_count;

			$(new_block).find('label').html("<?php echo $this->lang->line('taxes_tax_jurisdiction'); ?> " + tax_jurisdictions_count).attr('for', new_jurisdiction_name_id).attr('class', 'control-label col-xs-2');
			$(new_block).find("input[name='"+previous_jurisdiction_id+"']").attr('name', new_jurisdiction_id).val('-1');
			$(new_block).find("input[id='"+previous_jurisdiction_name_id+"']").attr('id', new_jurisdiction_name_id).removeAttr('disabled').attr('name', new_jurisdiction_name_id).attr('class', 'form-control required input-sm').val('');
			$(new_block).find("select[name='"+previous_tax_type_id+"']").attr('name', new_tax_type_id).removeAttr('disabled').attr('class', 'form-control required input-sm').val('');
			$(new_block).find("input[id='"+previous_reporting_authority_id+"']").attr('id', new_reporting_authority_id).removeAttr('disabled').attr('name', new_reporting_authority_id).attr('class', 'form-control input-sm').val('');
			$(new_block).find("input[id='"+previous_tax_group_sequence_id+"']").attr('id', new_tax_group_sequence_id).removeAttr('disabled').attr('name', new_tax_group_sequence_id).attr('class', 'form-control input-sm').val('');
			$(new_block).find("input[id='"+previous_cascade_sequence_id+"']").attr('id', new_cascade_sequence_id).removeAttr('disabled').attr('name', new_cascade_sequence_id).attr('class', 'form-control input-sm').val('');
			hide_show_remove();
		};

		var remove_tax_jurisdiction = function() {
			$(this).parent().remove();
			hide_show_remove();
		};

		var init_add_remove_tax_jurisdiction = function() {
			$('.add_tax_jurisdiction').click(add_tax_jurisdiction);
			$('.remove_tax_jurisdiction').click(remove_tax_jurisdiction);
			hide_show_remove();
		};
		init_add_remove_tax_jurisdiction();

		var duplicate_found = false;
		// run validator once for all fields
		$.validator.addMethod('tax_jurisdiction' , function(value, element) {
			var value_count = 0;
			$("input[name*='tax_jurisdiction']").each(function() {
				value_count = $(this).val() == value ? value_count + 1 : value_count;
			});
			return value_count < 2;
		}, "<?php echo $this->lang->line('taxes_tax_jurisdiction_duplicate'); ?>");

		$.validator.addMethod('valid_chars', function(value, element) {
			return value.indexOf('_') === -1;
		}, "<?php echo $this->lang->line('taxes_tax_jurisdiction_invalid_chars'); ?>");

		$('#tax_jurisdictions_form').validate($.extend(form_support.handler, {
			submitHandler: function(form) {
				$(form).ajaxSubmit({
					success: function(response)	{
						$.notify({ message: response.message }, { type: response.success ? 'success' : 'danger'});
						$("#tax_jurisdictions").load('<?php echo site_url("taxes/ajax_tax_jurisdictions"); ?>', init_add_remove_tax_jurisdiction);
					},
					dataType: 'json'
				});
			},

			errorLabelContainer: "#tax_jurisdiction_error_message_box",

			rules:
			{
			<?php
			$i = 0;

			foreach($tax_jurisdictions as $tax_jurisdiction=>$tax_jurisdiction_data)
			{
			?>
			<?php echo 'tax_jurisdiction_' . ++$i ?>:
			{
				required: true,
				tax_jurisdiction: true,
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

			foreach($tax_jurisdictions as $tax_jurisdiction=>$tax_jurisdiction_data)
			{
			?>
			<?php echo 'tax_jurisdiction_' . ++$i ?>: "<?php echo $this->lang->line('taxes_tax_jurisdiction_required'); ?>",
			<?php
			}
			?>
			}
		}));
	});
</script>
