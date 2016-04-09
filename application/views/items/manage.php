<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function()
{
    init_table_sorting();
    enable_select_all();
    enable_checkboxes();
    enable_row_selection();
	
    enable_search({suggest_url: "<?php echo site_url("$controller_name/suggest_search")?>",
        confirm_search_message: "<?php echo $this->lang->line("common_confirm_search")?>",
        extra_params: {
            'is_deleted' : function () {
				// the comparison is split in two parts: find the index of the selected and check the index against the index in the listed strings of the multiselect menu
                return $("#multi_filter li.selected").attr("data-original-index") == $("#filters option[value='is_deleted']").index() ? 1 : 0;
            }
        }
	});
	
    enable_delete("<?php echo $this->lang->line($controller_name."_confirm_delete")?>","<?php echo $this->lang->line($controller_name."_none_selected")?>");
    enable_bulk_edit("<?php echo $this->lang->line($controller_name."_none_selected")?>");

    $('#generate_barcodes').click(function()
    {
        var selected = get_selected_values();
        if (selected.length == 0)
        {
            alert("<?php echo $this->lang->line("items_must_select_item_for_barcode"); ?>");
            return false;
        }

        $(this).attr('href','index.php/items/generate_barcodes/'+selected.join(':'));
    });
	
	// when any filter is clicked and the dropdown window is closed
	$('#filters').on('hidden.bs.select', function(e)
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

	// load the preset datarange picker
	<?php $this->load->view('partial/daterangepicker'); ?>
	
	// set the beginning of time as starting date
	$('#daterangepicker').data('daterangepicker').setStartDate("<?php echo date($this->config->item('dateformat'), mktime(0,0,0,01,01,2010));?>");
	start_date = "<?php echo date('Y-m-d', mktime(0,0,0,01,01,2010));?>";

	// set default dates in the hidden inputs
	$("#start_date").val(start_date);
	$("#end_date").val(end_date);

	// update the hidden inputs with the selected dates before submitting the search data
	$("#daterangepicker").on('apply.daterangepicker', function(ev, picker) {
		$("#start_date").val(start_date);
		$("#end_date").val(end_date);

        // reset page number when selecting a specific page number
        $('#limit_from').val("0");
        do_search(true);
    });

    resize_thumbs();
});

function resize_thumbs()
{
    $('a.rollover').imgPreview();
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
	<div id="pagination" class="pull-left"><?php echo $links; ?></div>

	<?php echo anchor("$controller_name/excel_import",
		"<div class='btn btn-info btn-sm pull-right'><span>" . $this->lang->line('common_import_excel') . "</span></div>",
		array('class'=>'modal-dlg modal-btn-submit none', 'title'=>$this->lang->line('items_import_items_excel'))); ?>

	<?php echo anchor("$controller_name/view/-1",
		"<div class='btn btn-info btn-sm pull-right' style='margin-right: 10px;'><span>" . $this->lang->line($controller_name . '_new') . "</span></div>",
		array('class'=>'modal-dlg modal-btn-new modal-btn-submit', 'title'=>$this->lang->line($controller_name . '_new'))); ?>
</div>

<?php echo form_open("$controller_name/search", array('id'=>'search_form', 'class'=>'form-horizontal')); ?>
	<fieldset>
		<div id="table_action_header" class="form-group">
			<ul>
				<li class="pull-left"><?php echo anchor("$controller_name/delete", '<div class="btn btn-default btn-sm"><span>' . $this->lang->line("common_delete") . '</span></div>', array('id'=>'delete')); ?></li>
				<li class="pull-left"><?php echo anchor("$controller_name/bulk_edit", '<div class="btn btn-default btn-sm"><span>' . $this->lang->line("items_bulk_edit") . '</span></div>', array('id'=>'bulk_edit', 'class'=>'modal-dlg modal-btn-submit', 'title'=>$this->lang->line('items_edit_multiple_items'))); ?></li>
				<li class="pull-left"><?php echo anchor("$controller_name/generate_barcodes", '<div class="btn btn-default btn-sm"><span>' . $this->lang->line("items_generate_barcodes") . '</span></div>', array('id'=>'generate_barcodes', 'target' =>'_blank', 'title'=>$this->lang->line('items_generate_barcodes'))); ?></li>

				<li class="pull-right">
					<?php echo form_input(array('name'=>'search', 'class'=>'form-control input-sm', 'id'=>'search')); ?>
					<?php echo form_input(array('name'=>'limit_from', 'type'=>'hidden', 'id'=>'limit_from')); ?>
				</li>
				<li class="pull-right">
					<?php echo form_input(array('name'=>'daterangepicker', 'class'=>'form-control input-sm pull-right', 'id'=>'daterangepicker')); ?>
					<?php echo form_input(array('name'=>'start_date', 'type'=>'hidden', 'id'=>'start_date')); ?>
					<?php echo form_input(array('name'=>'end_date', 'type'=>'hidden', 'id'=>'end_date')); ?>
				</li>
				<li class="pull-right"><div id="multi_filter"><?php echo form_multiselect('filters[]', $filters, '', array('id'=>'filters', 'class'=>'selectpicker show-menu-arrow', 'data-selected-text-format'=>'count > 1', 'data-style'=>'btn-default btn-sm', 'data-width'=>'fit')); ?></div></li>
				<?php
				if (count($stock_locations) > 1)
				{
				?>
					<li class="pull-right"><?php echo form_dropdown('stock_location', $stock_locations, $stock_location, array('id'=>'stock_location', 'onchange'=>"$('#search_form').submit();", 'class'=>'selectpicker show-menu-arrow', 'data-style'=>'btn-default btn-sm', 'data-width'=>'fit')); ?></li>
				<?php
				}
				?>
			</ul>
		</div>
	</fieldset>
<?php echo form_close(); ?>

<div id="table_holder">
    <?php echo $manage_table; ?>
</div>

<?php $this->load->view("partial/footer"); ?>
