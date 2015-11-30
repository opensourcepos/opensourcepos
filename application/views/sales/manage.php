<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function()
{
	$.datepicker.regional[ '<?php echo $this->config->item('language'); ?>' ];
    init_table_sorting();
    enable_checkboxes();
    enable_row_selection();

	var on_complete = function(response) {
		$("#payment_summary").html(response.payment_summary);
	};

    enable_search({suggest_url : '<?php echo site_url("$controller_name/suggest")?>',
		confirm_search_message : '<?php echo $this->lang->line("common_confirm_search")?>',
		on_complete : on_complete});
    enable_delete('<?php echo $this->lang->line($controller_name."_confirm_delete")?>','<?php echo $this->lang->line($controller_name."_none_selected")?>');

	$("#search_filter_section #only_invoices").change(function() {
		do_search(true, on_complete);
		return false;
	});
	
	$("#search_filter_section #only_cash").change(function() {
		do_search(true, on_complete);
		return false;
	});

	var show_renumber = function() {
		var value = $("#only_invoices").val();
		var $button = $("#update_invoice_numbers").parents("li");
		$button.toggle(value === "1");
	};
	
	$("#only_invoices").change(show_renumber);
	show_renumber();

	$(".date_filter").datepicker({onSelect: function(d,i){
		if(d !== i.lastVal){
			$(this).change();
		}
	}, dateFormat: "<?php echo dateformat_jquery($this->config->item('dateformat'));?>"}).change(function() {
		do_search(true, function(response) {
			$("#payment_summary").html(response.payment_summary);
		});
		return false;
	});

	$("#update_invoice_numbers").click(function() {
		$.ajax({url : "<?php echo site_url('sales') ?>/update_invoice_numbers", dataType: 'json', success : post_bulk_form_submit });
		return false;
	});
});

function post_form_submit(response)
{
	if(!response.success)
	{
		set_feedback(response.message,'error_message',true);
	}
	else
	{
		update_row(response.id,'<?php echo site_url("$controller_name/get_row")?>');
		set_feedback(response.message,'success_message',false);
	}
}

function post_bulk_form_submit(response)
{
	if(!response.success)
	{
		set_feedback(response.message,'error_message',true);
	}
	else
	{
		for(id in response.ids)
		{
			update_row(response.ids[id],'<?php echo site_url("$controller_name/get_row")?>');
		}
		set_feedback(response.message,'success_message',false);
	}
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
	$.tablesorter.addParser({
	    id: "datetime",
	    is: function(s) {
	        return false; 
	    },
	    format: function(s,table) {
	        s = s.replace(/\-/g,"/");
	        s = s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})\s(\d{1,2})\:(\d{2})/, "$3/$2/$1 $4:$5");
	        return $.tablesorter.formatFloat(new Date(s).getTime());
	    },
	    type: "numeric"
	});

	$.tablesorter.addParser({
		id: "invoice_number",
		is: function(s) {
			return false;
		},
		format: function(s,table) {
			s = s.split(/[\/\-]/);
			if (s.length == 2 && s[0].match(/[12]\d{3}/g))
			{
				return $.tablesorter.formatFloat(new Date(s[0]).getTime() + s[1]);
			}
			return $.tablesorter.formatFloat(s);
		},
		type: "numeric"
	});

	$.tablesorter.addParser({
		id: "receipt_number",
		is: function(s) {
			return false;
		},
		format: function(s,table) {
			s = s.split(/[\s]/);
			if (s.length == 2 && s[1].match(/\d+/g))
			{
				return $.tablesorter.formatFloat(s[1]);
			}
			return s;
		},
		type: "numeric"
	});
		
	//Only init if there is more than one row
	if($('.tablesorter tbody tr').length > 1)
	{
		$("#sortable_table").tablesorter(
		{
			sortList: [[1,0]],
			dateFormat: '<?php echo dateformat_jquery($this->config->item('dateformat')); ?>',
			headers:
			{
			    0: { sorter: false},
				7: { sorter: 'false'},
				8: { sorter: 'invoice_number'},
				9: { sorter: 'false'}
			},
			widgets: ['staticRow']
		});
	}
}
</script>

<div id="title_bar">
	<div id="title" class="float_left"><?php echo $this->lang->line('common_list_of').' '.$this->lang->line('sales_receipt_number'); ?></div>
	<div id="new_button">
		<a href="javascript:window.print()"><div class='big_button' style='float: left;'><span><?php echo $this->lang->line('common_print'); ?></span></div></a>
	</div>
</div>
<div id="pagination"><?= $links ?></div>
<div id="titleTextImg" style="background-color:#EEEEEE;height:30px;position:relative;">
	<div style="float:left;vertical-align:text-top;"><?php echo $this->lang->line('common_search_options'). ': '; ?></div>
	<a id="imageDivLink" href="javascript:show_hide_search_filter('search_filter_section', 'imageDivLink');" style="outline:none;">
	<img src="<?php echo base_url().'images/plus.png'; ?>" style="border:0;outline:none;padding:0px;margin:0px;position:relative;top:-5px;"></a>
</div>
<?php echo form_open("$controller_name/search",array('id'=>'search_form')); ?>
<div id="search_filter_section" style="display: <?php echo isset($search_section_state)?  ( ($search_section_state)? 'block' : 'none') : 'none';?>;background-color:#EEEEEE;">
	<?php echo form_label($this->lang->line('sales_invoice_filter').' '.':', 'invoices_filter');?>
	<?php echo form_checkbox(array('name'=>'only_invoices','id'=>'only_invoices','value'=>1,'checked'=> isset($only_invoices)?  ( ($only_invoices)? 1 : 0) : 0)) . ' | ';?>
	<?php echo form_label($this->lang->line('sales_date_range').' :', 'start_date');?>
	<?php echo form_input(array('name'=>'start_date','value'=>$start_date, 'class'=>'date_filter', 'size' => '15'));?>
	<?php echo form_label(' - ', 'end_date');?>
	<?php echo form_input(array('name'=>'end_date','value'=>$end_date, 'class'=>'date_filter', 'size' => '15')) . ' | ';?>
	<?php echo form_label($this->lang->line('sales_cash_filter').' '.':', 'cash_filter');?>
	<?php echo form_checkbox(array('name'=>'only_cash','id'=>'only_cash','value'=>1,'checked'=> isset($only_cash)?  ( ($only_cash)? 1 : 0) : 0));?>
	<input type="hidden" name="search_section_state" id="search_section_state" value="<?php echo isset($search_section_state)?  ( ($search_section_state)? 'block' : 'none') : 'none';?>" />
</div>
<div id="table_action_header">
	<ul>
		<li class="float_left"><span><?php echo anchor($controller_name . "/delete",$this->lang->line("common_delete"),array('id'=>'delete')); ?></span></li>
		<!-- li class="float_left"><span><?php echo anchor($controller_name . "/update_invoice_numbers", $this->lang->line('sales_invoice_update'),array('id'=>'update_invoice_numbers')); ?></span></li-->
		<li class="float_right">
		<img src='<?php echo base_url()?>images/spinner_small.gif' alt='spinner' id='spinner' />
		<input type="text" name ='search' id='search'/>
		<input type="hidden" name ='limit_from' id='limit_from'/>
		</li>
	</ul>
</div>
<?php echo form_close(); ?>

<div id="table_holder" class="totals">
<?php echo $manage_table; ?>
</div>

<div id="payment_summary">
<?php echo $payments_summary; ?>
</div>

<div id="feedback_bar"></div>
<?php $this->load->view("partial/footer"); ?>