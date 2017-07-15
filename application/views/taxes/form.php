<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open('taxes/save/'.$tax_code, array('id'=>'tax_code_form', 'class'=>'form-horizontal')); ?>
	<fieldset id="tax_code_basic_info">
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('taxes_tax_code'), 'name', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-4'>
				<?php echo form_input(array(
						'name'=>'tax_code',
						'id'=>'tax_code',
						'class'=>'form-control input-sm text-uppercase',
						'value'=>$tax_code)
						);?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('taxes_tax_code_name'), 'name', array('class'=>'required control-label col-xs-3')); ?>
			<div class='col-xs-9'>
				<?php echo form_input(array(
						'name'=>'tax_code_name',
						'id'=>'tax_code_name',
						'class'=>'form-control input-sm',
						'value'=>$tax_code_name)
						);?>
			</div>
		</div>

        <div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('taxes_tax_code_type'), 'tax_code_type', !empty($basic_version) ? array('class'=>'required control-label col-xs-3') : array('class'=>'control-label col-xs-3')); ?>
            <div class="col-xs-9">
                <label class="radio-inline">
					<?php echo form_radio(array(
							'name'=>'tax_code_type',
							'type'=>'radio',
							'id'=>'tax_code_type',
							'value'=>0,
							'checked'=>$tax_code_type == '0')
					); ?> <?php echo $this->lang->line('taxes_sales_tax'); ?>
                </label>
                <label class="radio-inline">
					<?php echo form_radio(array(
							'name'=>'tax_code_type',
							'type'=>'radio',
							'id'=>'tax_code_type',
							'value'=>1,
							'checked'=>$tax_code_type == '1')
					); ?> <?php echo $this->lang->line('taxes_sales_tax_by_invoice'); ?>
                </label>
                <label class="radio-inline">
					<?php echo form_radio(array(
							'name'=>'tax_code_type',
							'type'=>'radio',
							'id'=>'tax_code_type',
							'value'=>1,
							'checked'=>$tax_code_type == '2')
					); ?> <?php echo $this->lang->line('taxes_vat_tax'); ?>
                </label>
            </div>
        </div>

        <div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('common_city'), 'city', array('class'=>'control-label col-xs-3')); ?>
            <div class='col-xs-8'>
				<?php echo form_input(array(
						'name'=>'city',
						'id'=>'city',
						'class'=>'form-control input-sm',
						'value'=>$city)
				);?>
            </div>
        </div>

        <div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('common_state'), 'name', array('class'=>'control-label col-xs-3')); ?>
            <div class='col-xs-8'>
				<?php echo form_input(array(
						'name'=>'state',
						'id'=>'state',
						'class'=>'form-control input-sm',
						'value'=>$state)
				);?>
            </div>
        </div>

        <div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('taxes_tax_rate'), 'tax_rate', array('class' => 'required control-label col-xs-3')); ?>
            <div class='col-xs-4'>
                <div class="input-group input-group-sm">
					<?php echo form_input(array(
							'name'=>'tax_rate',
							'id'=>'tax_rate',
							'class'=>'form-control input-sm',
							'value'=>$tax_rate)
					);?>
                    <span class="input-group-addon input-sm"><b>%</b></span>
                </div>
            </div>
        </div>

        <div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('taxes_rounding_code'), 'rounding_code', array('class' => 'control-label col-xs-3')); ?>
            <div class='col-xs-4'>
				<?php echo form_dropdown('rounding_code', $rounding_options, $rounding_code, array('class' => 'form-control input-sm')); ?>
            </div>
        </div>

        <div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('taxes_add_exception'), 'add_tax_category', array('class'=>'control-label col-xs-3')); ?>
            <div class='col-xs-8'>
                <div class="input-group">
                    <span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tag"></span></span>
					<?php echo form_input(array(
							'name'=>'add_tax_category',
							'id'=>'add_tax_category',
							'class'=>'form-control input-sm',
							'value'=>$add_tax_category)
					);?>
					<?php echo form_hidden('rate_tax_category_id', $rate_tax_category_id);?>
                </div>
            </div>
        </div>

        <table id="tax_code_rates" class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="10%"><?php echo $this->lang->line('common_delete'); ?></th>
                <th width="30%"><?php echo $this->lang->line('taxes_tax_category'); ?></th>
                <th width="30%"><?php echo $this->lang->line('taxes_tax_rate'); ?></th>
                <th width="30%"><?php echo $this->lang->line('taxes_rounding_code'); ?></th>
            </tr>
            </thead>
            <tbody>
			<?php
			foreach($tax_code_rates as $tax_code_rate)
			{
				?>
                <tr>
                    <td><a href='#' onclick='return delete_tax_code_rate_row(this);'><span class='glyphicon glyphicon-trash'></span></a></td>
                    <td><?php echo $tax_code_rate['tax_category']; ?></td>
                    <td><input class='form-control input-sm' id='exception_tax_rate_<?php echo $tax_code_rate['rate_tax_category_id'] ?>' name=exception_tax_rate[<?php echo $tax_code_rate['rate_tax_category_id'] ?>] value='<?php echo $tax_code_rate['tax_rate'] ?>'/></td>
                    <td><?php echo form_dropdown('exception_rounding_code['.$tax_code_rate['rate_tax_category_id'].']', $rounding_options, $tax_code_rate['rounding_code'], array('class' => 'form-control input-sm'));?></td>
                </tr>
				<?php
			}
			?>
            </tbody>
        </table>

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
                tax_code: "required",
                tax_rate: "required"
            },
            messages: {
                tax_code: "<?php echo $this->lang->line('taxes_tax_code_required'); ?>",
                tax_rate: "<?php echo $this->lang->line('taxes_tax_rate_required'); ?>"
            }
        }, form_support.error));


        $("#add_tax_category").autocomplete({
            source: '<?php echo site_url("taxes/suggest_tax_categories"); ?>',
            minChars: 0,
            autoFocus: false,
            delay: 10,
            appendTo: ".modal-content",
            select: function (e, ui) {

                var rounding_options = "<?php echo $html_rounding_options; ?>";

                if ($("#tax_category_id" + ui.item.value).length == 1) {
                    $("#tax_category_id" + ui.item.value).val(parseFloat($("#tax_category_id" + ui.item.value).val()) + 1);
                } else {
                    $("#tax_code_rates").append("<tr>" +
                        "<td><a href='#' onclick='return delete_tax_code_rate_row(this);'><span class='glyphicon glyphicon-trash'></span></a></td>" +
                        "<td>" + ui.item.label + "</td>" +
                        "<td><input class='form-control input-sm' id='exception_tax_rate_" + ui.item.value + "' name=exception_tax_rate[" + ui.item.value + "] value=''/></td>" +
                        "<td><select id='exception_rounding_code_" + ui.item.value + "' class='form-control input-sm' name=exception_rounding_code[" + ui.item.value +
                        "] aria-invalid='false'>" + rounding_options + "</select></td>" +
                        "</tr>");
                }
                $("#add_tax_category").val("");
                return false;
            }
        });


    });

    function delete_tax_code_rate_row(link) {
        $(link).parent().parent().remove();
        return false;
    }

</script>
