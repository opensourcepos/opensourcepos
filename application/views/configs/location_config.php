<div id="page_title"><?php echo $this->lang->line('config_location_configuration'); ?></div>
<?php
echo form_open('config/save_locations/',array('id'=>'location_config_form'));
?>
    <div id="config_wrapper">
        <fieldset id="config_info">
            <div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
            <ul id="location_error_message_box" class="error_message_box"></ul>
            <legend><?php echo $this->lang->line("config_location_info"); ?></legend>
            
            <div id="stock_locations">
				<?php $this->load->view('partial/stock_locations', array('stock_locations' => $stock_locations)); ?>
			</div>
            
            <?php 
            echo form_submit(array(
                'name'=>'submit',
                'id'=>'submit',
                'value'=>$this->lang->line('common_submit'),
                'class'=>'submit_button float_right')
            );
            ?>
        </fieldset>
    </div>
<?php
echo form_close();
?>


<script type='text/javascript'>

//validation and submit handling
$(document).ready(function()
{
	var location_count = <?php echo sizeof($stock_locations); ?>;

	var hide_show_remove = function() 
	{
		if ($("input[name*='stock_location']:enabled").length > 1)
		{
			$(".remove_stock_location").show();
		} 
		else
		{
			$(".remove_stock_location").hide();
		}
	};

	var add_stock_location = function() 
	{
		var id = $(this).parent().find('input').attr('id');
		id = id.replace(/.*?_(\d+)$/g, "$1");
		var block = $(this).parent().clone(true);
		var new_block = block.insertAfter($(this).parent());
		var new_block_id = 'stock_location_' + ++id;
		$(new_block).find('label').html("<?php echo $this->lang->line('config_stock_location'); ?> " + ++location_count + ": ").attr('for', new_block_id);
		$(new_block).find('input').attr('id', new_block_id).attr('name', new_block_id).val('');
		$('.add_stock_location', new_block).click(add_stock_location);
		$('.remove_stock_location', new_block).click(remove_stock_location);
		hide_show_remove();
	};

	var remove_stock_location = function() 
	{
		$(this).parent().remove();
		hide_show_remove();
	};

	var init_add_remove_locations = function() 
	{
		$('.add_stock_location').click(add_stock_location);
		$('.remove_stock_location').click(remove_stock_location);
		hide_show_remove();
	};
	init_add_remove_locations();

	var duplicate_found = false;
	// run validator once for all fields
	$.validator.addMethod('stock_location' , function(value, element) 
	{
		var value_count = 0;
		$("input[name*='stock_location']").each(function() {
			value_count = $(this).val() == value ? value_count + 1 : value_count; 
		});
		return value_count < 2;
    }, "<?php echo $this->lang->line('config_stock_location_duplicate'); ?>");

    $.validator.addMethod('valid_chars', function(value, element)
	{
		return value.indexOf('_') === -1;
    }, "<?php echo $this->lang->line('config_stock_location_invalid_chars'); ?>");
	
	$('#location_config_form').validate({
		submitHandler:function(form)
		{
			$(form).ajaxSubmit({
			success:function(response)
			{
				if(response.success)
				{
					set_feedback(response.message,'success_message',false);		
				}
				else
				{
					set_feedback(response.message,'error_message',true);		
				}
				$("#stock_locations").load('<?php echo site_url("config/stock_locations");?>', init_add_remove_locations);
			},
			dataType:'json'
		});

		},
		errorLabelContainer: "#location_error_message_box",
 		wrapper: "li",
		rules: 
		{
    		stock_location: {
        		required:true,
				stock_location: true,
				valid_chars: true
    		}
   		},
		messages: 
		{
     		stock_location:"<?php echo $this->lang->line('config_stock_location_required'); ?>"
		}
	});
});
</script>
