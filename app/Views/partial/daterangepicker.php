<?php $this->lang->load("calendar"); $this->lang->load("date"); 
	if(empty($this->config->item('date_or_time_format')))
	{
?>
		$('#daterangepicker').css("width","180");
		var start_date = "<?php echo date('Y-m-d') ?>";
		var end_date   = "<?php echo date('Y-m-d') ?>";

		$('#daterangepicker').daterangepicker({
			"ranges": {
				"<?php echo lang("datepicker_today"); ?>": [
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d"),date("Y")));?>",
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>"
				],
				"<?php echo lang("datepicker_today_last_year"); ?>": [
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d"),date("Y")-1));?>",
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y")-1)-1);?>"
				],
				"<?php echo lang("datepicker_yesterday"); ?>": [
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")-1,date("Y")));?>",
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d"),date("Y"))-1);?>"
				],
				"<?php echo lang("datepicker_last_7"); ?>": [
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")-6,date("Y")));?>",
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>"
				],
				"<?php echo lang("datepicker_last_30"); ?>": [
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")-29,date("Y")));?>",
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>"
				],
				"<?php echo lang("datepicker_this_month"); ?>": [
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),1,date("Y")));?>",
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m")+1,1,date("Y"))-1);?>"
				],
				"<?php echo lang("datepicker_same_month_to_same_day_last_year"); ?>": [
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),1,date("Y")-1));?>",
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y")-1)-1);?>"
				],
				"<?php echo lang("datepicker_same_month_last_year"); ?>": [
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),1,date("Y")-1));?>",
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m")+1,1,date("Y")-1)-1);?>"
				],
				"<?php echo lang("datepicker_last_month"); ?>": [
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m")-1,1,date("Y")));?>",
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),1,date("Y"))-1);?>"
				],
				"<?php echo lang("datepicker_this_year"); ?>": [
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,1,1,date("Y")));?>",
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),1,date("Y")+1)-1);?>"
				],
				"<?php echo lang("datepicker_last_year"); ?>": [
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,1,1,date("Y")-1));?>",
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,1,1,date("Y"))-1);?>"
				],
				"<?php echo lang("datepicker_this_financial_year"); ?>": [
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,$this->config->item('financial_year'),1,date("Y")));?>",
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),1,date("Y")+1)-1);?>"
				],
				"<?php echo lang("datepicker_last_financial_year"); ?>": [
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,$this->config->item('financial_year'),1,date("Y")-1));?>",
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,$this->config->item('financial_year'),1,date("Y"))-1);?>"
				],
				"<?php echo lang("datepicker_all_time"); ?>": [
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,1,1,2010));?>",
					"<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>"
				],
			},
			"locale": {
				"format": '<?php echo dateformat_momentjs($this->config->item("dateformat"))?>',
				"separator": " - ",
				"applyLabel": "<?php echo lang("datepicker_apply"); ?>",
				"cancelLabel": "<?php echo lang("datepicker_cancel"); ?>",
				"fromLabel": "<?php echo lang("datepicker_from"); ?>",
				"toLabel": "<?php echo lang("datepicker_to"); ?>",
				"customRangeLabel": "<?php echo lang("datepicker_custom"); ?>",
				"daysOfWeek": [
					"<?php echo lang("cal_su"); ?>",
					"<?php echo lang("cal_mo"); ?>",
					"<?php echo lang("cal_tu"); ?>",
					"<?php echo lang("cal_we"); ?>",
					"<?php echo lang("cal_th"); ?>",
					"<?php echo lang("cal_fr"); ?>",
					"<?php echo lang("cal_sa"); ?>",
					"<?php echo lang("cal_su"); ?>"
				],
				"monthNames": [
					"<?php echo lang("cal_january"); ?>",
					"<?php echo lang("cal_february"); ?>",
					"<?php echo lang("cal_march"); ?>",
					"<?php echo lang("cal_april"); ?>",
					"<?php echo lang("cal_may"); ?>",
					"<?php echo lang("cal_june"); ?>",
					"<?php echo lang("cal_july"); ?>",
					"<?php echo lang("cal_august"); ?>",
					"<?php echo lang("cal_september"); ?>",
					"<?php echo lang("cal_october"); ?>",
					"<?php echo lang("cal_november"); ?>",
					"<?php echo lang("cal_december"); ?>"
				],
				"firstDay": <?php echo lang("datepicker_weekstart"); ?>
			},
			"alwaysShowCalendars": true,
			"startDate": "<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>",
			"endDate": "<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>",
			"minDate": "<?php echo date($this->config->item('dateformat'), mktime(0,0,0,01,01,2010));?>",
			"maxDate": "<?php echo date($this->config->item('dateformat'), mktime(0,0,0,date("m"),date("d")+1,date("Y"))-1);?>"
		}, function(start, end, label) {
			start_date = start.format('YYYY-MM-DD');
			end_date = end.format('YYYY-MM-DD');
		});
<?php
	}
	else
	{
?>
		$('#daterangepicker').css("width","305");
		var start_date = "<?php echo date('Y-m-d H:i:s', mktime(0,0,0,date("m"),date("d"),date("Y")))?>";
		var end_date = "<?php echo date('Y-m-d H:i:s', mktime(23,59,59,date("m"),date("d"),date("Y")))?>";
		$('#daterangepicker').daterangepicker({
			"ranges": {
				"<?php echo lang("datepicker_today"); ?>": [
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'), mktime(0,0,0,date("m"),date("d"),date("Y")));?>",
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),date("d"),date("Y")));?>"
				],
				"<?php echo lang("datepicker_today_last_year"); ?>": [
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,date("m"),date("d"),date("Y")-1));?>",
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),date("d"),date("Y")-1));?>"
				],
				"<?php echo lang("datepicker_yesterday"); ?>": [
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,date("m"),date("d")-1,date("Y")));?>",
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),date("d")-1,date("Y")));?>"
				],
				"<?php echo lang("datepicker_last_7"); ?>": [
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,date("m"),date("d")-6,date("Y")));?>",
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),date("d"),date("Y")));?>"
				],
				"<?php echo lang("datepicker_last_30"); ?>": [
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,date("m"),date("d")-29,date("Y")));?>",
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),date("d"),date("Y")));?>"
				],
				"<?php echo lang("datepicker_this_month"); ?>": [
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,date("m"),1,date("Y")));?>",
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),date("d"),date("Y")));?>"
				],
				"<?php echo lang("datepicker_same_month_to_same_day_last_year"); ?>": [
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,date("m"),1,date("Y")-1));?>",
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),date("d"),date("Y")-1));?>"
				],
				"<?php echo lang("datepicker_same_month_last_year"); ?>": [
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,date("m"),1,date("Y")-1));?>",
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m")+1,0,date("Y")-1));?>"
				],
				"<?php echo lang("datepicker_last_month"); ?>": [
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,date("m")-1,1,date("Y")));?>",
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),0,date("Y")));?>"
				],
				"<?php echo lang("datepicker_this_year"); ?>": [
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,1,1,date("Y")));?>",
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m")+1,0,date("Y")));?>"
				],
				"<?php echo lang("datepicker_last_year"); ?>": [
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,1,1,date("Y")-1));?>",
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,12,31,date("Y")-1));?>"
				],
				"<?php echo lang("datepicker_this_financial_year"); ?>": [
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,$this->config->item('financial_year'),1,date("Y")));?>",
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m")+1,0,date("Y")));?>"
				],
				"<?php echo lang("datepicker_last_financial_year"); ?>": [
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,$this->config->item('financial_year'),1,date("Y")-1));?>",
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,$this->config->item('financial_year'),0,date("Y")));?>"
				],
				"<?php echo lang("datepicker_all_time"); ?>": [
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,1,1,2010));?>",
					"<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),date("d"),date("Y")));?>"
				],
			},
			"locale": {
				"format": '<?php echo dateformat_momentjs($this->config->item("dateformat")." ".$this->config->item('timeformat'))?>',
				"separator": " - ",
				"applyLabel": "<?php echo lang("datepicker_apply"); ?>",
				"cancelLabel": "<?php echo lang("datepicker_cancel"); ?>",
				"fromLabel": "<?php echo lang("datepicker_from"); ?>",
				"toLabel": "<?php echo lang("datepicker_to"); ?>",
				"customRangeLabel": "<?php echo lang("datepicker_custom"); ?>",
				"daysOfWeek": [
					"<?php echo lang("cal_su"); ?>",
					"<?php echo lang("cal_mo"); ?>",
					"<?php echo lang("cal_tu"); ?>",
					"<?php echo lang("cal_we"); ?>",
					"<?php echo lang("cal_th"); ?>",
					"<?php echo lang("cal_fr"); ?>",
					"<?php echo lang("cal_sa"); ?>",
					"<?php echo lang("cal_su"); ?>"
				],
				"monthNames": [
					"<?php echo lang("cal_january"); ?>",
					"<?php echo lang("cal_february"); ?>",
					"<?php echo lang("cal_march"); ?>",
					"<?php echo lang("cal_april"); ?>",
					"<?php echo lang("cal_may"); ?>",
					"<?php echo lang("cal_june"); ?>",
					"<?php echo lang("cal_july"); ?>",
					"<?php echo lang("cal_august"); ?>",
					"<?php echo lang("cal_september"); ?>",
					"<?php echo lang("cal_october"); ?>",
					"<?php echo lang("cal_november"); ?>",
					"<?php echo lang("cal_december"); ?>"
				],
				"firstDay": <?php echo lang("datepicker_weekstart"); ?>
			},
		    "timePicker": true,
		    "timePickerSeconds": true,
			"alwaysShowCalendars": true,
			"startDate": "<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,date("m"),date("d"),date("Y")));?>",
			"endDate": "<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),date("d"),date("Y")));?>",
			"minDate": "<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(0,0,0,01,01,2010));?>",
			"maxDate": "<?php echo date($this->config->item('dateformat')." ".$this->config->item('timeformat'),mktime(23,59,59,date("m"),date("d"),date("Y")));?>"
		}, function(start, end, label) {
			start_date = start.format('YYYY-MM-DD HH:mm:ss');
			end_date = end.format('YYYY-MM-DD HH:mm:ss');
		});
<?php
	}
?>
