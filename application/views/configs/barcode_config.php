<?php echo form_open('config/save_barcode/', array('id' => 'barcode_config_form', 'class' => 'form-horizontal')); ?>

<?php
$title_barcode['config_title'] = $this->lang->line('config_barcode_configuration');
$this->load->view('configs/config_header', $title_barcode);
?>

<ul id="barcode_error_message_box" class="error_message_box"></ul>

<div class="row">
	<div class="col-12 col-md-4 col-xl-3">
		<label for="barcode-type" class="form-label"><?= $this->lang->line('config_barcode_type'); ?></label>
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-upc"></i></label>
			<?= form_dropdown('barcode_type', $support_barcode, $this->config->item('barcode_type'), array('class' => 'form-select', 'id' => 'barcode-type')); ?>
		</div>
	</div>

	<div class="col-12 col-sm-6 col-md-4 col-xl-3">
		<label for="barcode-width" class="form-label"><?= $this->lang->line('config_barcode_width'); ?></label><span class="text-warning">*</span>
		<div class="input-group mb-3">
			<span class="input-group-text"><i class="bi bi-arrow-left-right"></i></span>
			<input type="number" min="60" max="350" name="barcode_width" class="form-control" id="barcode-width" value="<?= $this->config->item('barcode_width'); ?>" required>
			<span class="input-group-text">px</span>
		</div>
	</div>

	<div class="col-12 col-sm-6 col-md-4 col-xl-3">
		<label for="barcode-height" class="form-label"><?= $this->lang->line('config_barcode_height'); ?></label><span class="text-warning">*</span>
		<div class="input-group mb-3">
			<span class="input-group-text"><i class="bi bi-arrow-down-up"></i></span>
			<input type="number" min="10" max="120" name="barcode_height" class="form-control" id="barcode-height" value="<?= $this->config->item('barcode_height'); ?>" required>
			<span class="input-group-text">px</span>
		</div>
	</div>
</div>

<label for="barcode-type" class="form-label"><?= $this->lang->line('config_barcode_font'); ?></label>
<div class="row">
	<div class="col-12 col-md-8 col-xl-6">
		<div class="input-group mb-3">
			<label class="input-group-text"><i class="bi bi-fonts"></i></label>
			<?= form_dropdown('barcode_font', $this->barcode_lib->listfonts('fonts'), $this->config->item('barcode_font'), array('class' => 'form-select', 'id' => 'barcode-type')); ?>
		</div>
	</div>

	<div class="col-12 col-md-4 col-xl-3">
		<div class="input-group mb-3">
			<span class="input-group-text"><i class="bi bi-aspect-ratio"></i></span>
			<input type="number" min="1" max="30" name="barcode_font_size" class="form-control" value="<?= $this->config->item('barcode_font_size'); ?>" required>
			<span class="input-group-text">px</span>
		</div>
	</div>
</div>

<div class="form-check form-switch mb-3">
	<input class="form-check-input" type="checkbox" id="allow-duplicate-barcodes" name="allow_duplicate_barcodes" checked="<?= $this->config->item('allow_duplicate_barcodes'); ?>">
	<label class="form-check-label" for="allow-duplicate-barcodes"><?= $this->lang->line('config_allow_duplicate_barcodes'); ?></label>
	<i class="bi bi-info-circle-fill text-secondary" role="button" tabindex="0" data-bs-toggle="tooltip" title="<?= $this->lang->line('config_barcode_tooltip'); ?>"></i>
</div>

<div class="row mb-3">
	<div class="col-6 col-md-3">
		<div class="form-check">
			<input class="form-check-input" type="radio" name="barcode_content" id="barcode-content" checked="<?= $this->config->item('barcode_content'); ?>">
			<label class="form-check-label" for="barcode-content"><?= $this->lang->line('config_barcode_content'); ?></label>
		</div>
	</div>

	<div class="col-6 col-md-3">
		<div class="form-check">
			<input class="form-check-input" type="radio" name="barcode_content" id="barcode-number" value="number" checked="<?= $this->config->item('barcode_content'); ?>">
			<label class="form-check-label" for="barcode-number"><?= $this->lang->line('config_barcode_number'); ?></label>
		</div>
	</div>

	<div class="col-6 col-md-3">
		<div class="form-check">
			<input class="form-check-input" type="checkbox" name="barcode_generate_if_empty" id="barcode-generate-if-empty" checked="<?= $this->config->item('barcode_generate_if_empty'); ?>">
			<label class="form-check-label" for="barcode-generate-if-empty"><?= $this->lang->line('config_barcode_generate_if_empty'); ?></label>
		</div>
	</div>
</div>

<div class="form-group form-group-sm">
	<?php echo form_label($this->lang->line('config_barcode_formats'), 'barcode_formats', array('class' => 'control-label col-xs-2')); ?>
	<div class='col-xs-4'>
		<?php
		$barcode_formats = json_decode($this->config->item('barcode_formats'));
		echo form_dropdown(array(
			'name' => 'barcode_formats[]',
			'id' => 'barcode_formats',
			'options' => !empty($barcode_formats) ? array_combine($barcode_formats, $barcode_formats) : array(),
			'multiple' => 'multiple',
			'data-role' => 'tagsinput'
		)); ?>
	</div>
</div>

<div class="form-group form-group-sm">
	<?php echo form_label($this->lang->line('config_barcode_layout'), 'barcode_layout', array('class' => 'control-label col-xs-2')); ?>
	<div class="col-sm-10">
		<div class="form-group form-group-sm row">
			<label class="control-label col-sm-1"><?php echo $this->lang->line('config_barcode_first_row') . ' '; ?></label>
			<div class='col-sm-2'>
				<?php echo form_dropdown(
					'barcode_first_row',
					array(
						'not_show' => $this->lang->line('config_none'),
						'name' => $this->lang->line('items_name'),
						'category' => $this->lang->line('items_category'),
						'cost_price' => $this->lang->line('items_cost_price'),
						'unit_price' => $this->lang->line('items_unit_price'),
						'company_name' => $this->lang->line('suppliers_company_name')
					),
					$this->config->item('barcode_first_row'),
					array('class' => 'form-control input-sm')
				); ?>
			</div>
			<label class="control-label col-sm-1"><?php echo $this->lang->line('config_barcode_second_row') . ' '; ?></label>
			<div class='col-sm-2'>
				<?php echo form_dropdown(
					'barcode_second_row',
					array(
						'not_show' => $this->lang->line('config_none'),
						'name' => $this->lang->line('items_name'),
						'category' => $this->lang->line('items_category'),
						'cost_price' => $this->lang->line('items_cost_price'),
						'unit_price' => $this->lang->line('items_unit_price'),
						'item_code' => $this->lang->line('items_item_number'),
						'company_name' => $this->lang->line('suppliers_company_name')
					),
					$this->config->item('barcode_second_row'),
					array('class' => 'form-control input-sm')
				); ?>
			</div>
			<label class="control-label col-sm-1"><?php echo $this->lang->line('config_barcode_third_row') . ' '; ?></label>
			<div class='col-sm-2'>
				<?php echo form_dropdown(
					'barcode_third_row',
					array(
						'not_show' => $this->lang->line('config_none'),
						'name' => $this->lang->line('items_name'),
						'category' => $this->lang->line('items_category'),
						'cost_price' => $this->lang->line('items_cost_price'),
						'unit_price' => $this->lang->line('items_unit_price'),
						'item_code' => $this->lang->line('items_item_number'),
						'company_name' => $this->lang->line('suppliers_company_name')
					),
					$this->config->item('barcode_third_row'),
					array('class' => 'form-control input-sm')
				); ?>
			</div>
		</div>
	</div>
</div>

<div class="form-group form-group-sm">
	<?php echo form_label($this->lang->line('config_barcode_number_in_row'), 'barcode_num_in_row', array('class' => 'control-label col-xs-2 required')); ?>
	<div class='col-xs-2'>
		<?php echo form_input(array(
			'name' => 'barcode_num_in_row',
			'id' => 'barcode_num_in_row',
			'class' => 'form-control input-sm required',
			'value' => $this->config->item('barcode_num_in_row')
		)); ?>
	</div>
</div>

<div class="form-group form-group-sm">
	<?php echo form_label($this->lang->line('config_barcode_page_width'), 'barcode_page_width', array('class' => 'control-label col-xs-2 required')); ?>
	<div class="col-sm-2">
		<div class='input-group'>
			<?php echo form_input(array(
				'name' => 'barcode_page_width',
				'id' => 'barcode_page_width',
				'class' => 'form-control input-sm required',
				'value' => $this->config->item('barcode_page_width')
			)); ?>
			<span class="input-group-addon input-sm">%</span>
		</div>
	</div>
</div>

<div class="form-group form-group-sm">
	<?php echo form_label($this->lang->line('config_barcode_page_cellspacing'), 'barcode_page_cellspacing', array('class' => 'control-label col-xs-2 required')); ?>
	<div class='col-sm-2'>
		<div class="input-group">
			<?php echo form_input(array(
				'name' => 'barcode_page_cellspacing',
				'id' => 'barcode_page_cellspacing',
				'class' => 'form-control input-sm required',
				'value' => $this->config->item('barcode_page_cellspacing')
			)); ?>
			<span class="input-group-addon input-sm">px</span>
		</div>
	</div>
</div>

<div class="d-flex justify-content-end">
	<button class="btn btn-primary" name="submit_barcode"><?= $this->lang->line('common_submit'); ?></button>
</div>

<?php echo form_close(); ?>
<script type="text/javascript">
	//validation and submit handling
	$(document).ready(function() {
		$('#barcode_config_form').validate($.extend(form_support.handler, {

			errorLabelContainer: "#barcode_error_message_box",

			rules: {
				barcode_width: {
					required: true,
					number: true
				},
				barcode_height: {
					required: true,
					number: true
				},
				barcode_font_size: {
					required: true,
					number: true
				},
				barcode_num_in_row: {
					required: true,
					number: true
				},
				barcode_page_width: {
					required: true,
					number: true
				},
				barcode_page_cellspacing: {
					required: true,
					number: true
				}
			},

			messages: {
				barcode_width: {
					required: "<?php echo $this->lang->line('config_default_barcode_width_required'); ?>",
					number: "<?php echo $this->lang->line('config_default_barcode_width_number'); ?>"
				},
				barcode_height: {
					required: "<?php echo $this->lang->line('config_default_barcode_height_required'); ?>",
					number: "<?php echo $this->lang->line('config_default_barcode_height_number'); ?>"
				},
				barcode_font_size: {
					required: "<?php echo $this->lang->line('config_default_barcode_font_size_required'); ?>",
					number: "<?php echo $this->lang->line('config_default_barcode_font_size_number'); ?>"
				},
				barcode_num_in_row: {
					required: "<?php echo $this->lang->line('config_default_barcode_num_in_row_required'); ?>",
					number: "<?php echo $this->lang->line('config_default_barcode_num_in_row_number'); ?>"
				},
				barcode_page_width: {
					required: "<?php echo $this->lang->line('config_default_barcode_page_width_required'); ?>",
					number: "<?php echo $this->lang->line('config_default_barcode_page_width_number'); ?>"
				},
				barcode_page_cellspacing: {
					required: "<?php echo $this->lang->line('config_default_barcode_page_cellspacing_required'); ?>",
					number: "<?php echo $this->lang->line('config_default_barcode_page_cellspacing_number'); ?>"
				}
			}
		}));
	});
</script>