<?php echo form_open('config/save_tax/', array('id' => 'tax_config_form', 'class' => 'form-horizontal')); ?>
    <div id="config_wrapper">
        <fieldset id="config_info">
            <div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
            <ul id="tax_error_message_box" class="error_message_box"></ul>

            <div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_tax_included'), 'tax_included', array('class' => 'control-label col-xs-2')); ?>
                <div class='col-xs-2'>
					<?php echo form_checkbox(array(
						'name' => 'tax_included',
						'id' => 'tax_included',
						'value' => 'tax_included',
						'checked'=>$this->config->item('tax_included'))); ?>
                </div>
            </div>

            <div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_default_tax_rate_1'), 'default_tax_1_rate', array('class' => 'control-label col-xs-2')); ?>
                <div class='col-xs-2'>
					<?php echo form_input(array(
						'name' => 'default_tax_1_name',
						'id' => 'default_tax_1_name',
						'class' => 'form-control input-sm',
						'value'=>$this->config->item('default_tax_1_name')!==FALSE ? $this->config->item('default_tax_1_name') : $this->lang->line('items_sales_tax_1'))); ?>
                </div>
                <div class="col-xs-1 input-group">
					<?php echo form_input(array(
						'name' => 'default_tax_1_rate',
						'id' => 'default_tax_1_rate',
						'class' => 'form-control input-sm',
						'value'=> to_tax_decimals($this->config->item('default_tax_1_rate')))); ?>
                    <span class="input-group-addon input-sm">%</span>
                </div>
            </div>

            <div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_default_tax_rate_2'), 'default_tax_2_rate', array('class' => 'control-label col-xs-2')); ?>
                <div class='col-xs-2'>
					<?php echo form_input(array(
						'name' => 'default_tax_2_name',
						'id' => 'default_tax_2_name',
						'class' => 'form-control input-sm',
						'value'=>$this->config->item('default_tax_2_name')!==FALSE ? $this->config->item('default_tax_2_name') : $this->lang->line('items_sales_tax_2'))); ?>
                </div>
                <div class="col-xs-1 input-group">
					<?php echo form_input(array(
						'name' => 'default_tax_2_rate',
						'id' => 'default_tax_2_rate',
						'class' => 'form-control input-sm',
						'value'=> to_tax_decimals($this->config->item('default_tax_2_rate')))); ?>
                    <span class="input-group-addon input-sm">%</span>
                </div>
            </div>

            <div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_customer_sales_tax_support'), 'customer_sales_tax_support', array('class' => 'control-label col-xs-2')); ?>
                <div class='col-xs-2'>
					<?php echo form_checkbox(array(
						'name' => 'customer_sales_tax_support',
						'id' => 'customer_sales_tax_support',
						'value' => 'customer_sales_tax_support',
						'checked'=>$this->config->item('customer_sales_tax_support'))); ?>
                </div>
            </div>

            <div class="form-group form-group-sm">
				<?php echo form_label($this->lang->line('config_default_origin_tax_code'), 'default_origin_tax_code', array('class' => 'control-label col-xs-2')); ?>
                <div class='col-xs-2'>
					<?php echo form_dropdown('default_origin_tax_code', $tax_codes, $this->config->item('default_origin_tax_code'), array('class' => 'form-control input-sm')); ?>
                </div>
            </div>


            <div id="tax_categories">
				<?php $this->load->view('partial/tax_categories', array('tax_categories' => $tax_categories)); ?>
			</div>
            
            <?php echo form_submit(array(
                'name' => 'submit_tax',
                'id' => 'submit_tax',
                'value' => $this->lang->line('common_submit'),
                'class' => 'btn btn-primary btn-sm pull-right')); ?>
        </fieldset>
    </div>
<?php echo form_close(); ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{
    var enable_disable_customer_sales_tax_support = (function() {
        var customer_sales_tax_support = $("#customer_sales_tax_support").is(":checked");
//        $("input[name*='tax_category']:not(input[name=customer_sales_tax_support])").prop("disabled", !customer_sales_tax_support);
        $("input[name*='tax_category']").prop("disabled", !customer_sales_tax_support);
        $("input[name*='tax_group_sequence']").prop("disabled", !customer_sales_tax_support);
        $("select[name='default_origin_tax_code']").prop("disabled", !customer_sales_tax_support);
        if(customer_sales_tax_support)
        {
            $(".add_tax_category, .remove_tax_category").show();
        }
        else
        {
            $(".add_tax_category, .remove_tax_category").hide();
        }

        return arguments.callee;
    })();

    $("#customer_sales_tax_support").change(enable_disable_customer_sales_tax_support);


	var category_count = <?php echo sizeof($tax_categories); ?>;

	var hide_show_remove = function() {
		if ($("input[name*='tax_categories']:enabled").length > 1)
		{
			$(".remove_tax_categories").show();
		} 
		else
		{
			$(".remove_tax_categories").hide();
		}
	};


    var add_tax_category = function() {
        var id = $(this).parent().find('input').attr('id');
        id = id.replace(/.*?_(\d+)$/g, "$1");
        var previous_id = 'tax_category_' + id;
        var previous_id_next = 'tax_group_sequence_' + id;
        var block = $(this).parent().clone(true);
        var new_block = block.insertAfter($(this).parent());
        var new_block_id = 'tax_category_' + ++id;
        var new_block_id_next = 'tax_group_sequence_' + id;
        $(new_block).find('label').html("<?php echo $this->lang->line('config_tax_category'); ?> " + ++category_count).attr('for', new_block_id).attr('class', 'control-label col-xs-2');
        $(new_block).find("input[id='"+previous_id+"']").attr('id', new_block_id).removeAttr('disabled').attr('name', new_block_id).attr('class', 'form-control input-sm').val('');
        $(new_block).find("input[id='"+previous_id_next+"']").attr('id', new_block_id_next).removeAttr('disabled').attr('name', new_block_id_next).attr('class', 'form-control input-sm').val('');
        hide_show_remove();
    };

    var remove_tax_category = function() {
        $(this).parent().remove();
        hide_show_remove();
    };

    var init_add_remove_categories = function() {
        $('.add_tax_category').click(add_tax_category);
        $('.remove_tax_category').click(remove_tax_category);
        hide_show_remove();
        // set back disabled state
        enable_disable_customer_sales_tax_support();
    };
    init_add_remove_categories();

    var duplicate_found = false;
    // run validator once for all fields
    $.validator.addMethod('tax_category' , function(value, element) {
        var value_count = 0;
        $("input[name*='tax_category']:not(input[name=customer_sales_tax_support])").each(function() {
            value_count = $(this).val() == value ? value_count + 1 : value_count;
        });
        return value_count < 2;
    }, "<?php echo $this->lang->line('config_tax_category_duplicate'); ?>");

    $.validator.addMethod('valid_chars', function(value, element) {
        return value.indexOf('_') === -1;
    }, "<?php echo $this->lang->line('config_tax_category_invalid_chars'); ?>");


    $('#tax_config_form').validate($.extend(form_support.handler, {
        submitHandler: function(form) {
            $(form).ajaxSubmit({
                beforeSerialize: function(arr, $form, options) {
                    $("input[name*='tax_category']:not(input[name=customer_sales_tax_support])").prop("disabled", false);
                    return true;
                },
                success: function(response)	{
                    $.notify({ message: response.message }, { type: response.success ? 'success' : 'danger'});
                    $("#tax_categories").load('<?php echo site_url("config/ajax_tax_categories"); ?>', init_add_remove_categories);
                },
                dataType: 'json'
            });
        },

        errorLabelContainer: "#category_error_message_box",

        rules:
            {
                default_tax_1_rate:
                    {
                        remote: "<?php echo site_url($controller_name . '/check_numeric')?>"
                    },
                default_tax2_rate:
                    {
                        remote: "<?php echo site_url($controller_name . '/check_numeric')?>"
                    },

	<?php
	$i = 0;

	foreach($tax_categories as $tax_category=>$category)
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
        default_tax_1_rate:
        {
            number: "<?php echo $this->lang->line('config_default_tax_rate_number'); ?>"
        },
		<?php
		$i = 0;

		foreach($tax_categories as $tax_category=>$category)
		{
		?>
		<?php echo 'tax_category_' . ++$i ?>: "<?php echo $this->lang->line('config_tax_category_required'); ?>",
		<?php
		}
		?>
    }
}));


});
</script>
