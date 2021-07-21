<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open('attributes/save_definition/'.$definition_id, array('id'=>'attribute_form', 'class'=>'form-horizontal')); ?>
<fieldset id="attribute_basic_info">

	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('attributes_definition_name'), 'definition_name', array('class'=>'required control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<?php echo form_input(array(
					'name'=>'definition_name',
					'id' => 'definition_name',
					'class'=>'form-control input-sm',
					'value'=>$definition_info->definition_name)
			);?>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('attributes_definition_type'), 'definition_type', array('class'=>'required control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<?php echo form_dropdown('definition_type', DEFINITION_TYPES, array_search($definition_info->definition_type, DEFINITION_TYPES), 'id="definition_type" class="form-control"');?>
		</div>
	</div>

	<div class="form-group form-group-sm">
		<?php echo form_label($this->lang->line('attributes_definition_group'), 'definition_group', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<?php echo form_dropdown('definition_group', $definition_group, $definition_info->definition_fk, 'id="definition_group" class="form-control" ' . (empty($definition_group) ? 'disabled="disabled"' : ''));?>
		</div>
	</div>

	<div class="form-group form-group-sm hidden">
		<?php echo form_label($this->lang->line('attributes_definition_flags'), 'definition_flags', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<div class="input-group">
				<?php echo form_multiselect('definition_flags[]', $definition_flags, array_keys($selected_definition_flags), array('id'=>'definition_flags', 'class'=>'selectpicker show-menu-arrow', 'data-none-selected-text'=>$this->lang->line('common_none_selected_text'), 'data-selected-text-format'=>'count > 1', 'data-style'=>'btn-default btn-sm', 'data-width'=>'fit')); ?>
			</div>
		</div>
	</div>

	<div class="form-group form-group-sm hidden">
		<?php echo form_label($this->lang->line('attributes_definition_unit'), 'definition_units', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<div class="input-group">
				<?php echo form_input(array('name'=>'definition_unit', 'value'=>$definition_info->definition_unit,'class'=>'form-control input-sm', 'id' => 'definition_unit'));?>
			</div>
		</div>
	</div>

	<div class="form-group form-group-sm hidden">
		<?php echo form_label($this->lang->line('attributes_definition_values'), 'definition_value', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<div class="input-group">
				<?php echo form_input(array('name'=>'definition_value', 'class'=>'form-control input-sm', 'id' => 'definition_value'));?>
				<span id="add_attribute_value" class="input-group-addon input-sm btn btn-default">
					<span class="glyphicon glyphicon-plus-sign"></span>
				</span>
			</div>
		</div>
	</div>

	<div class="form-group form-group-sm hidden">
		<?php echo form_label('&nbsp', 'definition_list_group', array('class' => 'control-label col-xs-3')); ?>
		<div class='col-xs-8'>
			<ul id="definition_list_group" class="list-group"></ul>
		</div>
	</div>

</fieldset>
<?php echo form_close(); ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	var values = [];
	var definition_id = <?php echo $definition_id; ?>;
	var is_new = definition_id == 0;

	var disable_definition_types = function()
	{
		var definition_type = $("#definition_type option:selected").text();

		if(definition_type == "DATE" || (definition_type == "GROUP" && !is_new) || definition_type == "DECIMAL")
		{
			$('#definition_type').prop("disabled",true);
		}
		else if(definition_type == "DROPDOWN" || definition_type == "CHECKBOX")
		{
			$("#definition_type option:contains('GROUP')").hide();
			$("#definition_type option:contains('DATE')").hide();
			$("#definition_type option:contains('DECIMAL')").hide();
		}
		else
		{
			$("#definition_type option:contains('GROUP')").hide();
		}
	}
	disable_definition_types();

	var disable_category_dropdown = function()
	{
		if(definition_id == -1)
		{
			$('#definition_name').prop("disabled",true);
			$('#definition_type').prop("disabled",true);
			$('#definition_group').parents('.form-group').toggleClass("hidden", true);
			$('#definition_flags').parents('.form-group').toggleClass('hidden', true);
		}
	}
	disable_category_dropdown();

	var show_hide_fields = function(event)
	{
	    var is_dropdown = $('#definition_type').val() !== '1';
	    var is_decimal = $('#definition_type').val() !== '2';
	    var is_no_group = $('#definition_type').val() !== '0';
	    var is_category_dropdown = definition_id == -1;

		$('#definition_value, #definition_list_group').parents('.form-group').toggleClass('hidden', is_dropdown);
		$('#definition_unit').parents('.form-group').toggleClass('hidden', is_decimal);

	//Appropriately show definition flags if not category_dropdown
		if(definition_id != -1)
		{
			$('#definition_flags').parents('.form-group').toggleClass('hidden', !is_no_group);
		}
	};

	$('#definition_type').change(show_hide_fields);
	show_hide_fields();

	$('.selectpicker').each(function () {
		var $selectpicker = $(this);
		$.fn.selectpicker.call($selectpicker, $selectpicker.data());
	});

	var remove_attribute_value = function()
	{
		var value = $(this).parents("li").text();

		if (is_new)
		{
			values.splice($.inArray(value, values), 1);
		}
		else
		{
			$.post('<?php echo site_url($controller_name . "/delete_attribute_value/");?>', {definition_id: definition_id, attribute_value: value});
		}
		$(this).parents("li").remove();
	};

	var add_attribute_value = function(value)
	{
		var is_event = typeof(value) !== 'string';

        if ($("#definition_value").val().match(/(\||_)/g) != null)
        {
            return;
        }

		if (is_event)
		{
			value = $('#definition_value').val();

			if (!value)
			{
				return;
			}

			if (is_new)
			{
				values.push(value);
			}
			else
			{
				$.post('<?php echo site_url("attributes/save_attribute_value/");?>', {definition_id: definition_id, attribute_value: value});
			}
		}

		$('#definition_list_group').append("<li class='list-group-item'>" + value + "<a href='javascript:void(0);'><span class='glyphicon glyphicon-trash pull-right'></span></a></li>")
			.find(':last-child a').click(remove_attribute_value);
		$('#definition_value').val('');
	};

	$('#add_attribute_value').click(add_attribute_value);

	$('#definition_value').keypress(function (e) {
		if (e.which == 13) {
			add_attribute_value();
			return false;
		}
	});

	var definition_values = <?php echo json_encode(array_values($definition_values)) ?>;
	$.each(definition_values, function(index, element) {
		add_attribute_value(element);
	});

	$.validator.addMethod('valid_chars', function(value, element) {
        return value.match(/(\||_)/g) == null;
	}, "<?php echo $this->lang->line('attributes_attribute_value_invalid_chars'); ?>");

	$('form').bind('submit', function () {
		$(this).find(':input').prop('disabled', false);
	});

	$('#attribute_form').validate($.extend({
		submitHandler: function(form)
		{
			$(form).ajaxSubmit({
				beforeSerialize: function($form, options) {
					is_new && $('<input>').attr({
						id: 'definition_values',
						type: 'hidden',
						name: 'definition_values',
						value: JSON.stringify(values)
					}).appendTo($form);
				},
				success: function(response)
				{
					dialog_support.hide();
					table_support.handle_submit('<?php echo site_url($controller_name); ?>', response);
				},
				dataType: 'json'
			});
		},
		rules:
		{
			definition_name: 'required',
			definition_value: 'valid_chars',
			definition_type: 'required'
		},
        messages:
        {
            definition_name: "<?php echo $this->lang->line('attributes_definition_name_required'); ?>",
            definition_type: "<?php echo $this->lang->line('attributes_definition_type_required'); ?>"
        }
	}, form_support.error));
});
</script>