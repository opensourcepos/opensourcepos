<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function()
{
    init_table_sorting();
    enable_select_all();
    enable_checkboxes();
    enable_row_selection();
	
    var widget = enable_search({suggest_url : '<?php echo site_url("$controller_name/suggest_search")?>',
        confirm_search_message : '<?php echo $this->lang->line("common_confirm_search")?>',
        extra_params : {
            'is_deleted' : function () {
                return $("#is_deleted").is(":checked") ? 1 : 0;
        }
    }});
	
    enable_delete('<?php echo $this->lang->line($controller_name."_confirm_delete")?>','<?php echo $this->lang->line($controller_name."_none_selected")?>');
    enable_bulk_edit('<?php echo $this->lang->line($controller_name."_none_selected")?>');

    $('#generate_barcodes').click(function()
    {
        var selected = get_selected_values();
        if (selected.length == 0)
        {
            alert('<?php echo $this->lang->line('items_must_select_item_for_barcode'); ?>');
            return false;
        }

        $(this).attr('href','index.php/items/generate_barcodes/'+selected.join(':'));
    });

	// when any filter is clicked
    $("#search_filter_section input").click(function() 
    {
        // reset page number when selecting a specific page number
        $('#limit_from').val("0");
        do_search(true);
    });

	// accept partial suggestion to trigger a search on enter press
    $('#search').keypress(function (e) {
        if (e.which == 13) {
            $('#search_form').submit();
        }
    });

    <?php $this->load->view('partial/datepicker_locale'); ?>

	// initialise the datetime picker and trigger a search on any change date
    $(".date_filter").datetimepicker({
        format: "<?php echo dateformat_bootstrap($this->config->item('dateformat')); ?>",
		startDate: "<?php echo date($this->config->item('dateformat'), mktime(0, 0, 0, 1, 1, 2010));?>",
		minView: 2,
        autoclose: true,
        todayBtn: true,
        todayHighlight: true,
        bootcssVer: 3,
        language: "<?php echo $this->config->item('language'); ?>"
    }).on('changeDate', function(event) {
        do_search(true);
        return false;
    });

    resize_thumbs();
});

function resize_thumbs()
{
    $('a.rollover').imgPreview();
}

function show_hide_search_filter(search_filter_section, switchImgTag)
{
    var ele = document.getElementById(search_filter_section);
    var imageEle = document.getElementById(switchImgTag);
    if(ele.style.display == "block")
    {
		ele.style.display = "none";
		imageEle.innerHTML = '<img src=" <?php echo base_url()?>images/plus.png" style="border:0;outline:none;padding:0px;margin:0px;position:relative;top:-5px;" >';
    }
    else
    {
		ele.style.display = "block";
		imageEle.innerHTML = '<img src=" <?php echo base_url()?>images/minus.png" style="border:0;outline:none;padding:0px;margin:0px;position:relative;top:-5px;" >';
    }
}

function init_table_sorting()
{
    //Only init if there is more than one row
    if($('.tablesorter tbody tr').length >1)
    {
        $("#sortable_table").tablesorter(
        {
            sortList: [[1,0]],
            headers:
            {
                0: { sorter: 'false'},
                8: { sorter: 'false'},
                9: { sorter: 'false'},
                10: { sorter: 'false'},
                11: { sorter: 'false'},
                12: { sorter: 'false'}
            }
        });
    }
}

function post_item_form_submit(response)
{
    if(!response.success)
    {
        set_feedback(response.message, 'alert alert-dismissible alert-danger', true);
    }
    else
    {
        //This is an update, just update one row
        if(jQuery.inArray(response.item_id,get_visible_checkbox_ids()) != -1)
        {
            update_row(response.item_id,'<?php echo site_url("$controller_name/get_row")?>',resize_thumbs);
            set_feedback(response.message, 'alert alert-dismissible alert-success', false);
        }
        else //refresh entire table
        {
            do_search(true, function()
            {
                //highlight new row
                hightlight_row(response.item_id);
                set_feedback(response.message, 'alert alert-dismissible alert-success', false);
            });
        }
    }
}

function post_bulk_form_submit(response)
{
    if(!response.success)
    {
        set_feedback(response.message, 'alert alert-dismissible alert-danger', true);
    }
    else
    {
        var selected_item_ids=get_selected_values();
        for(k=0;k<selected_item_ids.length;k++)
        {
            update_row(selected_item_ids[k],'<?php echo site_url("$controller_name/get_row")?>',resize_thumbs);
        }
        set_feedback(response.message, 'alert alert-dismissible alert-success', false);
    }
}
</script>

<div id="title_bar">
    <div id="title" class="float_left"><?php echo $this->lang->line('common_list_of').' '.$this->lang->line('module_'.$controller_name); ?></div>
	<?php echo anchor("$controller_name/excel_import",
		"<div class='btn btn-info btn-sm pull-right'><span>" . $this->lang->line('common_import_excel') . "</span></div>",
		array('class'=>'modal-dlg modal-btn-submit none', 'title'=>$this->lang->line('items_import_items_excel'))); ?>

	<?php echo anchor("$controller_name/view/-1",
		"<div class='btn btn-info btn-sm pull-right' style='margin-right: 10px;'><span>" . $this->lang->line($controller_name . '_new') . "</span></div>",
		array('class'=>'modal-dlg modal-btn-new modal-btn-submit', 'title'=>$this->lang->line($controller_name . '_new'))); ?>
</div>

<div id="pagination"><?php echo $links; ?></div>

<div id="titleTextImg">
    <div style="float:left;vertical-align:text-top;"><?php echo $this->lang->line('common_search_options'). ': '; ?></div>
    <a id="imageDivLink" href="javascript:show_hide_search_filter('search_filter_section', 'imageDivLink');" style="outline:none;">
	<img src="<?php echo base_url().'images/plus.png'; ?>" style="border:0;outline:none;padding:0px;margin:0px;position:relative;top:-5px;"></a>
</div>

<?php echo form_open("$controller_name/search", array('id'=>'search_form', 'class'=>'form-horizontal')); ?>
	<fieldset>
		<div id="search_filter_section" class="form-group" style="display:none;">
			<?php echo form_label($this->lang->line('items_empty_upc_items').' '.':', 'empty_upc');?>
			<?php echo form_checkbox(array('name'=>'empty_upc','id'=>'empty_upc','value'=>1,'checked'=> isset($empty_upc)?  ( ($empty_upc)? 1 : 0) : 0)).' | ';?>
			<?php echo form_label($this->lang->line('items_low_inventory_items').' '.':', 'low_inventory');?>
			<?php echo form_checkbox(array('name'=>'low_inventory','id'=>'low_inventory','value'=>1,'checked'=> isset($low_inventory)?  ( ($low_inventory)? 1 : 0) : 0)).' | ';?>
			<?php echo form_label($this->lang->line('items_serialized_items').' '.':', 'is_serialized');?>
			<?php echo form_checkbox(array('name'=>'is_serialized','id'=>'is_serialized','value'=>1,'checked'=> isset($is_serialized)?  ( ($is_serialized)? 1 : 0) : 0)).' | ';?>
			<?php echo form_label($this->lang->line('items_no_description_items').' '.':', 'no_description');?>
			<?php echo form_checkbox(array('name'=>'no_description','id'=>'no_description','value'=>1,'checked'=> isset($no_description)?  ( ($no_description)? 1 : 0) : 0)).' | ';?>
			<?php echo form_label($this->lang->line('items_search_custom_items').' '.':', 'search_custom');?>
			<?php echo form_checkbox(array('name'=>'search_custom','id'=>'search_custom','value'=>1,'checked'=> isset($search_custom)?  ( ($search_custom)? 1 : 0) : 0)).' | ';?>
			<?php echo form_label($this->lang->line('items_is_deleted').' '.':', 'is_deleted');?> 
			<?php echo form_checkbox(array('name'=>'is_deleted','id'=>'is_deleted','value'=>1,'checked'=> isset($is_deleted)?  ( ($is_deleted)? 1 : 0) : 0));?> 

			<?php echo form_label($this->lang->line('sales_date_range').' :', 'start_date');?>
			<?php echo form_input(array('name'=>'start_date','value'=>$start_date, 'class'=>'date_filter', 'size'=>'15'));?>
			<?php echo form_label(' - ', 'end_date');?>
			<?php echo form_input(array('name'=>'end_date','value'=>$end_date, 'class'=>'date_filter', 'size'=>'15'));?>
		</div>

		<div id="table_action_header" class="form-group">
			<ul>
				<li class="float_left"><?php echo anchor("$controller_name/delete", '<div class="btn btn-default btn-sm"><span>' . $this->lang->line("common_delete") . '</span></div>', array('id'=>'delete')); ?></li>
				<li class="float_left"><?php echo anchor("$controller_name/bulk_edit", '<div class="btn btn-default btn-sm"><span>' . $this->lang->line("items_bulk_edit") . '</span></div>', array('id'=>'bulk_edit', 'class'=>'modal-dlg modal-btn-submit', 'title'=>$this->lang->line('items_edit_multiple_items'))); ?></li>
				<li class="float_left"><?php echo anchor("$controller_name/generate_barcodes", '<div class="btn btn-default btn-sm"><span>' . $this->lang->line("items_generate_barcodes") . '</span></div>', array('id'=>'generate_barcodes', 'target' =>'_blank', 'title'=>$this->lang->line('items_generate_barcodes'))); ?></li>
				<?php
				if (count($stock_locations) > 1)
				{
				?>
					<li class="float_left"><?php echo form_dropdown('stock_location', $stock_locations, $stock_location, array('id'=>'stock_location', 'onchange'=>"$('#search_form').submit();", 'class'=>'selectpicker show-menu-arrow', 'data-style'=>'btn-default btn-sm', 'data-width'=>'fit')); ?></li>
				<?php
				}
				?>
				<li class="float_right"><input type="text" name="search" id="search", class="form-control input-sm"/><input type="hidden" name="limit_from" id="limit_from"/></li>
			</ul>
		</div>
	</fieldset>
<?php echo form_close(); ?>

<div id="table_holder">
    <?php echo $manage_table; ?>
</div>

<?php $this->load->view("partial/footer"); ?>
