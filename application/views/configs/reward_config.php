<?php echo form_open('config/save_rewards/', array('id' => 'reward_config_form', 'class' => 'form-horizontal')); ?>
    <div id="config_wrapper">
        <fieldset id="config_info">
            <div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
            <ul id="reward_error_message_box" class="error_message_box"></ul>

			<div class="form-group form-group-sm">	
				<?php echo form_label($this->lang->line('config_customer_reward_enable'), 'customer_reward_enable', array('class' => 'control-label col-xs-2')); ?>
				<div class='col-xs-1'>
					<?php echo form_checkbox(array(
						'name' => 'customer_reward_enable',
						'value' => 'customer_reward_enable',
						'id' => 'customer_reward_enable',
						'checked' => $this->config->item('customer_reward_enable')));?>
				</div>
			</div>

            <div id="customer_rewards">
				<?php $this->load->view('partial/customer_rewards', array('customer_rewards' => $customer_rewards)); ?>
			</div>
            
            <?php echo form_submit(array(
                'name' => 'submit',
                'id' => 'submit',
                'value'=>$this->lang->line('common_submit'),
                'class' => 'btn btn-primary btn-sm pull-right')); ?>
        </fieldset>
    </div>
<?php echo form_close(); ?>

<script type="text/javascript">
//validation and submit handling
$(document).ready(function()
{

	var enable_disable_customer_reward_enable = (function() {
		var customer_reward_enable = $("#customer_reward_enable").is(":checked");
		$("input[name*='customer_reward']:not(input[name=customer_reward_enable])").prop("disabled", !customer_reward_enable);
		$("input[name*='reward_points_']:not(input[name=customer_reward_enable])").prop("disabled", !customer_reward_enable);
		if(customer_reward_enable)
		{
			$(".add_customer_reward, .remove_customer_reward").show();
		}
		else
		{
			$(".add_customer_reward, .remove_customer_reward").hide();	
		}
		return arguments.callee;
	})();

	$("#customer_reward_enable").change(enable_disable_customer_reward_enable);

	var table_count = <?php echo sizeof($customer_rewards); ?>;

	var hide_show_remove = function() {
		if ($("input[name*='customer_rewards']:enabled").length > 1)
		{
			$(".remove_customer_rewards").show();
		} 
		else
		{
			$(".remove_customer_rewards").hide();
		}
	};

	var add_customer_reward = function() {
		var id = $(this).parent().find('input').attr('id');
		id = id.replace(/.*?_(\d+)$/g, "$1");
		var previous_id = 'customer_reward_' + id;
		var previous_id_next = 'reward_points_' + id;
		var block = $(this).parent().clone(true);
		console.log(block);
		var new_block = block.insertAfter($(this).parent());
		var new_block_id = 'customer_reward_' + ++id;
		var new_block_id_next = 'reward_points_' + id;
		$(new_block).find('label').html("<?php echo $this->lang->line('config_customer_reward'); ?> " + ++table_count).attr('for', new_block_id).attr('class', 'control-label col-xs-2');
		$(new_block).find("input[id='"+previous_id+"']").attr('id', new_block_id).removeAttr('disabled').attr('name', new_block_id).attr('class', 'form-control input-sm').val('');
		$(new_block).find("input[id='"+previous_id_next+"']").attr('id', new_block_id_next).removeAttr('disabled').attr('name', new_block_id_next).attr('class', 'form-control input-sm').val('');
		hide_show_remove();
	};

	var remove_customer_reward = function() {
		$(this).parent().remove();
		hide_show_remove();
	};

	var init_add_remove_tables = function() {
		$('.add_customer_reward').click(add_customer_reward);
		$('.remove_customer_reward').click(remove_customer_reward);
		hide_show_remove();
		// set back disabled state
		enable_disable_customer_reward_enable();
	};
	init_add_remove_tables();

	var duplicate_found = false;
	// run validator once for all fields
	$.validator.addMethod('customer_reward' , function(value, element) {
		var value_count = 0;
		$("input[name*='customer_reward']:not(input[name=customer_reward_enable])").each(function() {
			value_count = $(this).val() == value ? value_count + 1 : value_count; 
		});
		return value_count < 2;
    }, "<?php echo $this->lang->line('config_customer_reward_duplicate'); ?>");

    $.validator.addMethod('valid_chars', function(value, element) {
		return value.indexOf('_') === -1;
    }, "<?php echo $this->lang->line('config_customer_reward_invalid_chars'); ?>");
	
	$('#reward_config_form').validate($.extend(form_support.handler, {
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				beforeSerialize: function(arr, $form, options) {
					$("input[name*='customer_reward']:not(input[name=customer_reward_enable])").prop("disabled", false); 
					return true;
				},
				success: function(response)	{
					$.notify({ message: response.message }, { type: response.success ? 'success' : 'danger'});
					$("#customer_rewards").load('<?php echo site_url("config/ajax_customer_rewards"); ?>', init_add_remove_tables);
				},
				dataType: 'json'
			});
		},

		errorLabelContainer: "#reward_error_message_box",

		rules:
		{
			<?php
			$i = 0;

			foreach($customer_rewards as $customer_reward=>$table)
			{
			?>
				<?php echo 'customer_reward_' . ++$i ?>:
				{
					required: true,
					customer_reward: true,
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

			foreach($customer_rewards as $customer_reward=>$table)
			{
			?>
				<?php echo 'customer_reward_' . ++$i ?>: "<?php echo $this->lang->line('config_customer_reward_required'); ?>",
			<?php
			}
			?>
		}
	}));
});
</script>
