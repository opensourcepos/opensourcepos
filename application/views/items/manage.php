<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function()
{
    /*enable_search({suggest_url: '<?php echo site_url("$controller_name/suggest_search")?>',
        confirm_search_message: "<?php echo $this->lang->line("common_confirm_search")?>",
        extra_params: {
            'is_deleted' : function () {
				// the comparison is split in two parts: find the index of the selected and check the index against the index in the listed strings of the multiselect menu
                return $("#multi_filter li.selected").attr("data-original-index") == $("#filters option[value='is_deleted']").index() ? 1 : 0;
            }
        }
	});*/

    $('#generate_barcodes').click(function()
    {
        $(this).attr('href','index.php/items/generate_barcodes/'+selected.join(':'));
    });
	
	// when any filter is clicked and the dropdown window is closed
	$('#filters').on('hidden.bs.select', function(e)
	{
        table_support.refresh();
    });

	// load the preset datarange picker
	<?php $this->load->view('partial/daterangepicker'); ?>
    // set the beginning of time as starting date
    $('#daterangepicker').data('daterangepicker').setStartDate("<?php echo date($this->config->item('dateformat'), mktime(0,0,0,01,01,2010));?>");
	// update the hidden inputs with the selected dates before submitting the search data
    var start_date = "<?php echo date('Y-m-d', mktime(0,0,0,01,01,2010));?>";
	$("#daterangepicker").on('apply.daterangepicker', function(ev, picker) {
        table_support.refresh();
    });

    table_support.init('<?php echo site_url($controller_name);?>', <?php echo $table_headers; ?>, function() {
        return $.extend(arguments[0], {
            start_date: start_date,
            end_date: end_date,
            filters: $("#filters").val() || [""]
        });
    });
    table_support.init_delete('<?php echo $this->lang->line($controller_name."_confirm_delete")?>');

    resize_thumbs();
});

function resize_thumbs()
{
    $('a.rollover').imgPreview();
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

<div id="title_bar" class="btn-toolbar">

    <button class='btn btn-info btn-sm pull-right modal-dlg modal-btn-submit' data-href='<?php echo site_url($controller_name."/excel_import"); ?>'
            title='<?php echo $this->lang->line('customers_import_items_excel'); ?>'>
        <span class="glyphicon glyphicon-import"></span><?php echo $this->lang->line('common_import_excel'); ?>
    </button>

    <button class='btn btn-info btn-sm pull-right modal-dlg modal-btn-submit' data-href='<?php echo site_url($controller_name."/view"); ?>'
            title='<?php echo $this->lang->line($controller_name . '_new'); ?>'>
        <span class="glyphicon glyphicon-tag"></span><?php echo $this->lang->line($controller_name. '_new'); ?>
    </button>

</div>

<div id="toolbar">
    <div class="pull-left form-inline" role="toolbar">
        <button id="delete" class="btn btn-default btn-sm" data-href='<?php echo site_url($controller_name."/delete"); ?>'>
            <span class="glyphicon glyphicon-trash"></span>
            <?php echo $this->lang->line("common_delete");?>
        </button>
        <button id="bulk_edit" class="btn btn-default btn-sm" data-href='<?php echo site_url($controller_name."/bulk_edit"); ?>' title='<?php $this->lang->line('items_edit_multiple_items');?>'>
            <span class="glyphicon glyphicon-edit"></span>
            <?php echo $this->lang->line("items_bulk_edit"); ?>
        </button>
        <button id="generate_barcodes" class="btn btn-default btn-sm" data-href='<?php echo site_url($controller_name."/generate_barcodes"); ?>' title='<?php echo $this->lang->line('items_generate_barcodes');?>'>
            <span class="glyphicon glyphicon-barcode"></span>
            <?php echo $this->lang->line("items_generate_barcodes");?>
        </button>
        <?php echo form_input(array('name'=>'daterangepicker', 'class'=>'form-control input-sm', 'id'=>'daterangepicker')); ?>
        <?php echo form_multiselect('filters[]', $filters, '', array('id'=>'filters', 'class'=>'selectpicker show-menu-arrow', 'data-selected-text-format'=>'count > 1', 'data-style'=>'btn-default btn-sm', 'data-width'=>'fit')); ?>
        <?php
        if (count($stock_locations) > 1)
        {
            echo form_dropdown('stock_location', $stock_locations, $stock_location, array('id'=>'stock_location', 'class'=>'selectpicker show-menu-arrow', 'data-style'=>'btn-default btn-sm', 'data-width'=>'fit'));
        }
        ?>
    </div>
</div>

<div id="table_holder">
    <table id="table"></table>
</div>

<?php $this->load->view("partial/footer"); ?>
