<?php
/**
 * @var array $stock_locations
 */
?>
<?php echo form_open('config/save_locations/', ['id' => 'location_config_form', 'class' => 'form-horizontal']) ?>
    <div id="config_wrapper">
        <fieldset id="config_info">
            <div id="required_fields_message"><?php echo lang('Common.fields_required_message') ?></div>
            <ul id="stock_error_message_box" class="error_message_box"></ul>

            <div id="stock_locations">
				<?php echo view('partial/stock_locations', ['stock_locations' => $stock_locations]) ?>
			</div>
            
            <?php echo form_submit ([
                'name' => 'submit_stock',
                'id' => 'submit_stock',
                'value' => lang('Common.submit'),
                'class' => 'btn btn-primary btn-sm pull-right'
			]) ?>
        </fieldset>
    </div>
<?php echo form_close() ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
	var location_count = <?php echo sizeof($stock_locations) ?>;

	var hide_show_remove = function() {
		if ($("input[name*='stock_location']:enabled").length > 1)
		{
			$(".remove_stock_location").show();
		} 
		else
		{
			$(".remove_stock_location").hide();
		}
	};

	var add_stock_location = function() {
		var block = $(this).parent().clone(true);
		var new_block = block.insertAfter($(this).parent());
		var new_block_id = 'stock_location[]';
		$(new_block).find('label').html("<?php echo lang('Config.stock_location') ?> " + ++location_count).attr('for', new_block_id).attr('class', 'control-label col-xs-2');
		$(new_block).find('input').attr('id', new_block_id).removeAttr('disabled').attr('name', new_block_id).attr('class', 'form-control input-sm').val('');
		hide_show_remove();
	};

	var remove_stock_location = function() {
		$(this).parent().remove();
		hide_show_remove();
	};

	var init_add_remove_locations = function() {
		$('.add_stock_location').click(add_stock_location);
		$('.remove_stock_location').click(remove_stock_location);
		hide_show_remove();
	};
	init_add_remove_locations();

	var duplicate_found = false;
	// run validator once for all fields
	$.validator.addMethod('stock_location' , function(value, element) {
		var value_count = 0;
		$("input[name*='stock_location']").each(function() {
			value_count = $(this).val() == value ? value_count + 1 : value_count; 
		});
		return value_count < 2;
    }, "<?php echo lang('Config.stock_location_duplicate') ?>");

    $.validator.addMethod('valid_chars', function(value, element) {
		return value.indexOf('_') === -1;
    }, "<?php echo lang('Config.stock_location_invalid_chars') ?>");
	
	$('#location_config_form').validate($.extend(form_support.handler, {
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				success: function(response)	{
					$.notify({ message: response.message }, { type: response.success ? 'success' : 'danger'});
					$("#stock_locations").load('<?php echo "config/ajax_stock_locations" ?>', init_add_remove_locations);
				},
				dataType: 'json'
			});
		},

		errorLabelContainer: "#stock_error_message_box",

		rules:
		{
			<?php
			$i = 0;

			foreach($stock_locations as $location => $location_data)
			{
			?>
				<?php echo 'stock_location_' . ++$i ?>:
				{
					required: true,
					stock_location: true,
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

			foreach($stock_locations as $location => $location_data)
			{
			?>
				<?php echo 'stock_location_' . ++$i ?>: "<?php echo lang('Config.stock_location_required') ?>",
			<?php
			}
			?>
		}
	}));
});
</script>
