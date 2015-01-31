<?php
define('IN_CB', true);
include_once(APPPATH."libraries/barcodegen/html/include/function.php");
?>


<div id="page_title"><?php echo $this->lang->line('config_barcode_configuration'); ?></div>
<?php
echo form_open('config/save_barcode/',array('id'=>'barcode_config_form'));
?>
    <div id="config_wrapper">
        <fieldset id="config_info">
            <div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>
            <ul id="error_message_box"></ul>
            <legend><?php echo $this->lang->line("config_barcode_info"); ?></legend>
            
            <div class="field_row clearfix">    
            <?php echo form_label($this->lang->line('config_barcode_type').':', 'barcode_type',array('class'=>'wide')); ?>
                <div class='form_field'>
                <?php echo form_dropdown('barcode_type', $support_barcode, $this->config->item('barcode_type'));?>
                </div>
            </div>
            
            <div class="field_row clearfix">    
            <?php echo form_label($this->lang->line('config_barcode_dpi').':', 'barcode_dpi',array('class'=>'wide required')); ?>
                <div class='form_field'>
                <?php echo form_input(array(
                    'max'=>'300',
                    'min'=>'72',
                    'type'=>'number',
                    'name'=>'barcode_dpi',
                    'id'=>'barcode_dpi',
                    'value'=>$this->config->item('barcode_dpi')));?>
                </div>
            </div>
            
            <div class="field_row clearfix">    
            <?php echo form_label($this->lang->line('config_barcode_thickness').':', 'barcode_thickness',array('class'=>'wide required')); ?>
                <div class='form_field'>
                <?php echo form_input(array(
                    'step'=>'5',
                    'max'=>'90',
                    'min'=>'20',
                    'type'=>'number',
                    'name'=>'barcode_thickness',
                    'id'=>'barcode_thickness',
                    'value'=>$this->config->item('barcode_thickness')));?>
                </div>
            </div>

            <div class="field_row clearfix">    
            <?php echo form_label($this->lang->line('config_barcode_scale').':', 'barcode_scale',array('class'=>'wide required')); ?>
                <div class='form_field'>
                <?php echo form_input(array(
                    'type' => 'number',
                    'min' => 1,
                    'max' => 4,
                    'name'=>'barcode_scale',
                    'id'=>'barcode_scale',
                    'value'=>$this->config->item('barcode_scale')));?>
                </div>
            </div>
            
            <div class="field_row clearfix">    
            <?php echo form_label($this->lang->line('config_barcode_rotation').':', 'barcode_rotation',array('class'=>'wide')); ?>
                <div class='form_field'>
                <?php echo form_dropdown('barcode_rotation', array(
                        'no_rotation'        => 'No rotation',
                        '90'   => '90&deg; clockwise',
                        '180'           => '180&deg; clockwise',
                        '270'           => '270&deg; clockwise'
                        ), 
                    $this->config->item('barcode_rotation'));
                    ?>
                </div>
            </div>

            <div class="field_row clearfix">    
            <?php echo form_label($this->lang->line('config_barcode_font').':', 'barcode_font',array('class'=>'wide required')); ?>
                <div class='form_field'>
                <?php echo form_dropdown('barcode_font', 
                   listfonts("application/libraries/barcodegen/font"), 
                    $this->config->item('barcode_font'));
                    ?>
                    
                <?php echo form_input(array(
                    'type' => 'number', 
                    'min' => '1', 
                    'max' => '30',
                    'name'=>'barcode_font_size',
                    'id'=>'barcode_font_size',
                    'value'=>$this->config->item('barcode_font_size')));?>
                </div>
            </div>
                       
            <div class="field_row clearfix">    
            <?php echo form_label($this->lang->line('config_barcode_checksum').':', 'barcode_checksum',array('class'=>'wide')); ?>
                <div class='form_field'>
                <?php echo form_checkbox(array(
                    'name'=>'barcode_checksum',
                    'id'=>'barcode_checksum',
                    'value'=>'barcode_checksum',
                    'checked'=>$this->config->item('barcode_checksum')));?>
                </div>
            </div>

            <div class="field_row clearfix">    
            <?php echo form_label($this->lang->line('config_barcode_layout').':', 'barcode_layout',array('class'=>'wide')); ?>
                <div class='form_field'>
                <?php echo $this->lang->line('config_barcode_first_row').' '; ?>
                <?php echo form_dropdown('barcode_first_row', array(
                        'not_show'        => 'Not show',
                        'name'        => 'Name',
                        'category'   => 'Category',
                        'item_code'           => 'Item code',
                        'cost_price'           => 'Cost price',
                        'unit_price'           => 'Unit price'
                        ), 
                    $this->config->item('barcode_first_row'));
                    ?>
                <?php echo $this->lang->line('config_barcode_second_row').' '; ?>    
                <?php echo form_dropdown('barcode_second_row', array(
                        'not_show'        => 'Not show',
                        'name'        => 'Name',
                        'category'   => 'Category',
                        'item_code'           => 'Item code',
                        'cost_price'           => 'Cost price',
                        'unit_price'           => 'Unit price'
                        ), 
                    $this->config->item('barcode_second_row'));
                    ?>
                <?php echo $this->lang->line('config_barcode_third_row').' '; ?>    
                <?php echo form_dropdown('barcode_third_row', array(
                        'not_show'        => 'Not show',
                        'name'        => 'Name',
                        'category'   => 'Category',
                        'item_code'           => 'Item code',
                        'cost_price'           => 'Cost price',
                        'unit_price'           => 'Unit price'
                        ), 
                    $this->config->item('barcode_third_row'));
                    ?>
                </div>
            </div>
            
            <div class="field_row clearfix">    
            <?php echo form_label($this->lang->line('config_barcode_number_in_row').':', 'barcode_num_in_row',array('class'=>'wide required')); ?>
                <div class='form_field'>
                <?php echo form_input(array(
                    'name'=>'barcode_num_in_row',
                    'id'=>'barcode_num_in_row',
                    'value'=>$this->config->item('barcode_num_in_row')));?>
                </div>
            </div>
            
            <div class="field_row clearfix">    
            <?php echo form_label($this->lang->line('config_barcode_page_width').':', 'barcode_page_width',array('class'=>'wide required')); ?>
                <div class='form_field'>
                <?php echo form_input(array(
                    'name'=>'barcode_page_width',
                    'id'=>'barcode_page_width',
                    'value'=>$this->config->item('barcode_page_width')));?>
                %
                </div>
            </div>
            
            <div class="field_row clearfix">    
            <?php echo form_label($this->lang->line('config_barcode_page_cellspacing').':', 'barcode_page_cellspacing',array('class'=>'wide required')); ?>
                <div class='form_field'>
                <?php echo form_input(array(
                    'name'=>'barcode_page_cellspacing',
                    'id'=>'barcode_page_cellspacing',
                    'value'=>$this->config->item('barcode_page_cellspacing')));?>
                pixl
                </div>
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
    $('#barcode_config_form').validate({
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
            },
            dataType:'json'
        });

        },
        errorLabelContainer: "#error_message_box",
        wrapper: "li",
        rules: 
        {
            barcode_dpi: 
            {
                required:true,
                number:true
            },
            barcode_thickness: 
            {
                required:true,
                number:true
            },
            barcode_scale: 
            {
                required:true,
                number:true
            },
            barcode_font_size:
            {
                required:true,
                number:true
            },
            barcode_num_in_row:
            {
                required:true,
                number:true
            },
            barcode_page_width:
            {
                required:true,
                number:true
            },
            barcode_page_cellspacing:
            {
                required:true,
                number:true
            }        
        },
        messages: 
        {
            barcode_dpi:
            {
                required:"<?php echo $this->lang->line('config_default_barcode_dpi_required'); ?>",
                number:"<?php echo $this->lang->line('config_default_barcode_dpi_number'); ?>"
            },
            barcode_thickness:
            {
                required:"<?php echo $this->lang->line('config_default_barcode_thickness_required'); ?>",
                number:"<?php echo $this->lang->line('config_default_barcode_thickness_number'); ?>"
            },
            barcode_scale:
            {
                required:"<?php echo $this->lang->line('config_default_barcode_scale_required'); ?>",
                number:"<?php echo $this->lang->line('config_default_barcode_scale_number'); ?>"
            },
            barcode_font_size:
            {
                required:"<?php echo $this->lang->line('config_default_barcode_font_size_required'); ?>",
                number:"<?php echo $this->lang->line('config_default_barcode_font_size_number'); ?>"
            },
            barcode_num_in_row:
            {
                required:"<?php echo $this->lang->line('config_default_barcode_num_in_row_required'); ?>",
                number:"<?php echo $this->lang->line('config_default_barcode_num_in_row_number'); ?>"
            },
            barcode_page_width:
            {
                required:"<?php echo $this->lang->line('config_default_barcode_page_width_required'); ?>",
                number:"<?php echo $this->lang->line('config_default_barcode_page_width_number'); ?>"
            },
            barcode_page_cellspacing:
            {
                required:"<?php echo $this->lang->line('config_default_barcode_page_cellspacing_required'); ?>",
                number:"<?php echo $this->lang->line('config_default_barcode_page_cellspacing_number'); ?>"
            }                            
        }
    });
});
</script>
