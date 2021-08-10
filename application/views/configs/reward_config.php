<?= form_open('config/save_rewards/', array('id' => 'reward_config_form', 'class' => 'form-horizontal')); ?>

<?php
$title_reward['config_title'] = $this->lang->line('config_reward_configuration');
$this->load->view('configs/config_header', $title_reward);
?>

<ul id="reward_error_message_box" class="error_message_box"></ul>

<div class="form-check form-switch mb-3">
	<input class="form-check-input" name="customer_reward_enable" type="checkbox" id="customer-reward-enable" value="customer_reward_enable" checked="<?= $this->config->item('customer_reward_enable'); ?>">
	<label class="form-check-label" name="customer_reward_enable" for="customer-reward-enable"><?= $this->lang->line('config_customer_reward_enable'); ?></label>
</div>

<label for="reward-tiers" class="form-label">Reward Tiers</label>
<div class="row" id="reward-tiers">
	<div class="col-12 col-md-6">
		<div class="input-group mb-3">
			<span class="input-group-text">1</span>
			<input type="text" aria-label="Reward name" class="form-control">
			<input type="number" aria-label="Reward value" class="form-control">
		</div>
	</div>

	<div class="col-12 col-md-6">
		<div class="input-group mb-3">
			<span class="input-group-text">2</span>
			<input type="text" aria-label="Reward name" class="form-control">
			<input type="number" aria-label="Reward value" class="form-control">
		</div>
	</div>

	<div class="col-12 col-md-6">
		<div class="input-group mb-3">
			<span class="input-group-text">3</span>
			<input type="text" aria-label="Reward name" class="form-control">
			<input type="number" aria-label="Reward value" class="form-control">
		</div>
	</div>

	<div class="col-12 col-md-6">
		<div class="input-group mb-3">
			<span class="input-group-text">4</span>
			<input type="text" aria-label="Reward name" class="form-control">
			<input type="number" aria-label="Reward value" class="form-control">
		</div>
	</div>

	<div class="col-12 col-md-6">
		<div class="input-group mb-3">
			<span class="input-group-text">5</span>
			<input type="text" aria-label="Reward name" class="form-control">
			<input type="number" aria-label="Reward value" class="form-control">
		</div>
	</div>
</div>

<div class="d-flex justify-content-end">
	<button class="btn btn-primary" name="submit_reward"><?= $this->lang->line('common_submit'); ?></button>
</div>

<br><br><br>
<div id="customer_rewards">
	<?php $this->load->view('partial/customer_rewards', array('customer_rewards' => $customer_rewards)); ?>
</div>

<?= form_close(); ?>

<script type="text/javascript">
	//validation and submit handling
	document.querySelector(document).ready(function() {

var enable_disable_customer_reward_enable = (function() {
	var customer_reward_enable = document.querySelector("#customer_reward_enable").is(":checked");
	document.querySelector("input[name*='customer_reward']:not(input[name=customer_reward_enable])").prop("disabled", !customer_reward_enable);
	document.querySelector("input[name*='reward_points_']:not(input[name=customer_reward_enable])").prop("disabled", !customer_reward_enable);
	if (customer_reward_enable) {
		document.querySelector(".add_customer_reward, .remove_customer_reward").show();
	} else {
		document.querySelector(".add_customer_reward, .remove_customer_reward").hide();
	}
	return arguments.callee;
})();

document.querySelector("#customer_reward_enable").change(enable_disable_customer_reward_enable);

var table_count = <?= sizeof($customer_rewards); ?>;

var hide_show_remove = function() {
	if (document.querySelector("input[name*='customer_rewards']:enabled").length > 1) {
		document.querySelector(".remove_customer_rewards").show();
	} else {
		document.querySelector(".remove_customer_rewards").hide();
	}
};

var add_customer_reward = function() {
	var id = document.querySelector(this).parent().querySelector('input').attr('id');
	id = id.replace(/.*?_(d+)$/g, "$1");
	var previous_id = 'customer_reward_' + id;
	var previous_id_next = 'reward_points_' + id;
	var block = document.querySelector(this).parent().clone(true);
	var new_block = block.insertAfter(document.querySelector(this).parent());
	var new_block_id = 'customer_reward_' + ++id;
	var new_block_id_next = 'reward_points_' + id;
	document.querySelector(new_block).querySelector('label').html("<?= $this->lang->line('config_customer_reward'); ?> " + ++table_count).attr('for', new_block_id).attr('class', 'control-label col-xs-2');
	document.querySelector(new_block).querySelector("input[id='" + previous_id + "']").attr('id', new_block_id).removeAttr('disabled').attr('name', new_block_id).attr('class', 'form-control input-sm').val('');
	document.querySelector(new_block).querySelector("input[id='" + previous_id_next + "']").attr('id', new_block_id_next).removeAttr('disabled').attr('name', new_block_id_next).attr('class', 'form-control input-sm').val('');
	hide_show_remove();
};

var remove_customer_reward = function() {
	document.querySelector(this).parent().remove();
	hide_show_remove();
};

var init_add_remove_tables = function() {
	document.querySelector('.add_customer_reward').click(add_customer_reward);
	document.querySelector('.remove_customer_reward').click(remove_customer_reward);
	hide_show_remove();
	// set back disabled state
	enable_disable_customer_reward_enable();
};
init_add_remove_tables();

var duplicate_found = false;
// run validator once for all fields
$.validator.addMethod('customer_reward', function(value, element) {
	var value_count = 0;
	document.querySelector("input[name*='customer_reward']:not(input[name=customer_reward_enable])").each(function() {
		value_count = document.querySelector(this).value == value ? value_count + 1 : value_count;
	});
	return value_count < 2;
}, "<?= $this->lang->line('config_customer_reward_duplicate'); ?>");

$.validator.addMethod('valid_chars', function(value, element) {
	return value.indexOf('_') === -1;
}, "<?= $this->lang->line('config_customer_reward_invalid_chars'); ?>");

document.querySelector('#reward_config_form').validate($.extend(form_support.handler, {
	submitHandler: function(form) {
		document.querySelector(form).ajaxSubmit({
			beforeSerialize: function(arr, $form, options) {
				document.querySelector("input[name*='customer_reward']:not(input[name=customer_reward_enable])").prop("disabled", false);
				return true;
			},
			success: function(response) {
				$.notify({
					message: response.message
				}, {
					type: response.success ? 'success' : 'danger'
				});
				document.querySelector("#customer_rewards").load('<?= site_url("config/ajax_customer_rewards"); ?>', init_add_remove_tables);
			},
			dataType: 'json'
		});
	},

	errorLabelContainer: "#reward_error_message_box",

	rules: {
		<?php
		$i = 0;

		foreach ($customer_rewards as $customer_reward => $table) {
		?>
			<?= 'customer_reward_' . ++$i ?>: {
				required: true,
				customer_reward: true,
				valid_chars: true
			},
		<?php
		}
		?>
	},

	messages: {
		<?php
		$i = 0;

		foreach ($customer_rewards as $customer_reward => $table) {
		?>
			<?= 'customer_reward_' . ++$i ?>: "<?= $this->lang->line('config_customer_reward_required'); ?>",
		<?php
		}
		?>
	}
}));
});
</script>