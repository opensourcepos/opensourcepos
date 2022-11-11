
<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open('taxes/save/'.$tax_rate_id, array('id'=>'tax_code_form', 'class'=>'form-horizontal')); ?>
	<fieldset id="tax_rate_info">
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('taxes_tax_code'), 'rate_tax_code_id', array('class' => 'control-label col-xs-3')); ?>
			<div class='col-xs-5'>
				<?php echo form_dropdown('rate_tax_code_id', $tax_code_options, $rate_tax_code_id, array('class' => 'form-control input-sm')); ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('taxes_tax_category'), 'rate_tax_category_id', array('class' => 'control-label col-xs-3')); ?>
			<div class='col-xs-5'>
				<?php echo form_dropdown('rate_tax_category_id', $tax_category_options, $rate_tax_category_id, array('class' => 'form-control input-sm')); ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('taxes_tax_jurisdiction'), 'rate_jurisdiction_id', array('class' => 'control-label col-xs-3')); ?>
			<div class='col-xs-5'>
				<?php echo form_dropdown('rate_jurisdiction_id', $tax_jurisdiction_options, $rate_jurisdiction_id, array('class' => 'form-control input-sm')); ?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('taxes_tax_rate'), 'tax_rate', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-5 input-group' style='padding-left:15px;' >
				<?php echo form_input(array(
						'name'=>'tax_rate',
						'id'=>'tax_rate',
						'class'=>'form-control input-sm text-uppercase',
						'value'=>$tax_rate)
				);?>
				<span class="input-group-addon input-sm">%</span>
			</div>

		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('taxes_tax_rounding'), 'tax_rounding_code', array('class' => 'control-label col-xs-3')); ?>
			<div class='col-xs-5'>
				<?php echo form_dropdown('tax_rounding_code', $rounding_options, $tax_rounding_code, array('class' => 'form-control input-sm'));
				?>
			</div>
		</div>
	</fieldset>

<?php echo form_close(); ?>

<script type="text/javascript">
    //validation and submit handling
    $(document).ready(function() {

        $('#tax_code_form').validate($.extend({
            submitHandler: function (form) {
                $(form).ajaxSubmit({
                    success: function (response) {
                        dialog_support.hide();
                        table_support.handle_submit('<?php echo site_url('taxes'); ?>', response);
                    },
                    dataType: 'json'
                });
            },
            rules: {
            },
            messages: {
            }
        }, form_support.error));


    });

    function delete_tax_rate_row(link) {
        $(link).parent().parent().remove();
        return false;
    }

</script>
