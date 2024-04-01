<?php
/**
 * @var object $person_info
 * @var array $config
 */
?>
<div class="form-group form-group-sm">
	<?= form_label(lang('Common.first_name'), 'first_name', ['class' => 'required control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<?= form_input ([
			'name' => 'first_name',
			'id' => 'first_name',
			'class' => 'form-control input-sm',
			'value' => $person_info->first_name
		]) ?>
	</div>
</div>

<div class="form-group form-group-sm">
	<?= form_label(lang('Common.last_name'), 'last_name', ['class' => 'required control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<?= form_input ([
			'name' => 'last_name',
			'id' => 'last_name',
			'class' => 'form-control input-sm',
			'value' => $person_info->last_name
		]) ?>
	</div>
</div>

<div class="form-group form-group-sm">
	<?= form_label(lang('Common.gender'), 'gender', !empty($basic_version) ? ['class' => 'required control-label col-xs-3'] : ['class' => 'control-label col-xs-3']) ?>
	<div class="col-xs-4">
		<label class="radio-inline">
			<?= form_radio ([
				'name' => 'gender',
				'type' => 'radio',
				'id' => 'gender',
				'value'=>1,
				'checked' => $person_info->gender === '1'
			]) ?> <?= lang('Common.gender_male') ?>
		</label>
		<label class="radio-inline">
			<?= form_radio ([
				'name' => 'gender',
				'type' => 'radio',
				'id' => 'gender',
				'value' => 0,
				'checked' => $person_info->gender === '0'
			]) ?> <?= lang('Common.gender_female') ?>
		</label>

	</div>
</div>

<div class="form-group form-group-sm">
	<?= form_label(lang('Common.email'), 'email', ['class' => 'control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<div class="input-group">
			<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-envelope"></span></span>
			<?= form_input ([
				'name' => 'email',
				'id' => 'email',
				'class' => 'form-control input-sm',
				'value' => $person_info->email
			]) ?>
		</div>
	</div>
</div>

<div class="form-group form-group-sm">
	<?= form_label(lang('Common.phone_number'), 'phone_number', ['class' => 'control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<div class="input-group">
			<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-phone-alt"></span></span>
			<?= form_input ([
				'name' => 'phone_number',
				'id' => 'phone_number',
				'class' => 'form-control input-sm',
				'value' => $person_info->phone_number
			]) ?>
		</div>
	</div>
</div>

<div class="form-group form-group-sm">
	<?= form_label(lang('Common.address_1'), 'address_1', ['class' => 'control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<?= form_input ([
			'name' => 'address_1',
			'id' => 'address_1',
			'class' => 'form-control input-sm',
			'value' => $person_info->address_1
		]) ?>
	</div>
</div>

<div class="form-group form-group-sm">
	<?= form_label(lang('Common.address_2'), 'address_2', ['class' => 'control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<?= form_input ([
			'name' => 'address_2',
			'id' => 'address_2',
			'class' => 'form-control input-sm',
			'value' => $person_info->address_2
		]) ?>
	</div>
</div>

<div class="form-group form-group-sm">
	<?= form_label(lang('Common.city'), 'city', ['class' => 'control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<?= form_input ([
			'name' => 'city',
			'id' => 'city',
			'class' => 'form-control input-sm',
			'value' => $person_info->city
		]) ?>
	</div>
</div>

<div class="form-group form-group-sm">
	<?= form_label(lang('Common.state'), 'state', ['class' => 'control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<?= form_input ([
			'name' => 'state',
			'id' => 'state',
			'class' => 'form-control input-sm',
			'value' => $person_info->state
		]) ?>
	</div>
</div>

<div class="form-group form-group-sm">
	<?= form_label(lang('Common.zip'), 'zip', ['class' => 'control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<?= form_input ([
			'name' => 'zip',
			'id' => 'postcode',
			'class' => 'form-control input-sm',
			'value' => $person_info->zip
		]) ?>
	</div>
</div>

<div class="form-group form-group-sm">
	<?= form_label(lang('Common.country'), 'country', ['class' => 'control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<?= form_input ([
			'name' => 'country',
			'id' => 'country',
			'class' => 'form-control input-sm',
			'value' => $person_info->country
		]) ?>
	</div>
</div>

<div class="form-group form-group-sm">
	<?= form_label(lang('Common.comments'), 'comments', ['class' => 'control-label col-xs-3']) ?>
	<div class='col-xs-8'>
		<?= form_textarea ([
			'name' => 'comments',
			'id' => 'comments',
			'class' => 'form-control input-sm',
			'value' => $person_info->comments
		]) ?>
	</div>
</div>

<script type="application/javascript">
//validation and submit handling
$(document).ready(function()
{
	nominatim.init({
		fields : {
			postcode : {
				dependencies :  ["postcode", "city", "state", "country"],
				response : {
					field : 'postalcode',
					format: ["postcode", "village|town|hamlet|city_district|city", "state", "country"]
				}
			},

			city : {
				dependencies :  ["postcode", "city", "state", "country"],
				response : {
					format: ["postcode", "village|town|hamlet|city_district|city", "state", "country"]
				}
			},

			state : {
				dependencies :  ["state", "country"]
			},

			country : {
				dependencies :  ["state", "country"]
			}
		},
		language : '<?= current_language_code() ?>',
		country_codes: '<?= esc($config['country_codes'], 'js') ?>'
	});
});
</script>
