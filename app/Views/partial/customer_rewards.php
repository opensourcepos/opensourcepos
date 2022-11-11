<?php
$i = 0;

foreach($customer_rewards as $customer_rewards => $reward_category)
{
	$customer_reward_id = $reward_category['package_id'];
	$customer_reward_name = $reward_category['package_name'];
	$customer_points_percent = $reward_category['points_percent'];
	++$i;
?>
	<div class="form-group form-group-sm" style="<?php echo $reward_category['deleted'] ? 'display:none;' : 'display:block;' ?>">
		<?php echo form_label($this->lang->line('config_customer_reward') . ' ' . $i, 'customer_reward_' . $i, array('class'=>'required control-label col-xs-2')); ?>
		<div class='col-xs-2'>
			<?php $form_data = array(
					'name'=>'customer_reward_' . $customer_reward_id,
					'id'=>'customer_reward_' . $customer_reward_id,
					'class'=>'customer_reward valid_chars form-control input-sm required',
					'value'=>$customer_reward_name
				);
				$reward_category['deleted'] && $form_data['disabled'] = 'disabled';
				echo form_input($form_data);
			?>
		</div>
		<div class='col-xs-2'>
			<?php $form_data = array(
					'name'=>'reward_points_' . $customer_reward_id,
					'id'=>'reward_points_' . $customer_reward_id,
					'class'=>'customer_reward valid_chars form-control input-sm required',
					'value'=>$customer_points_percent
				);
				$reward_category['deleted'] && $form_data['disabled'] = 'disabled';
				echo form_input($form_data);
			?>
		</div>
		<span class="add_customer_reward glyphicon glyphicon-plus" style="padding-top: 0.5em;"></span>
		<span>&nbsp;&nbsp;</span>
		<span class="remove_customer_reward glyphicon glyphicon-minus" style="padding-top: 0.5em;"></span>
	</div>
<?php
}
?>
