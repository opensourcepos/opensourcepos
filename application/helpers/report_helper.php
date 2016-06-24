<?php

function show_report_if_allowed($report_prefix, $report_name, $person_id, $permission_id = '')
{
	$CI =& get_instance();

	$permission_id = empty($permission_id) ? 'reports_' . $report_name : $permission_id;	
	if($CI->Employee->has_grant($permission_id, $person_id))
	{
		show_report($report_prefix, $report_name, $permission_id);
	}
}

function show_report($report_prefix, $report_name, $lang_key='')
{
	$CI =& get_instance();

	$lang_key = empty($lang_key) ? $report_name : $lang_key;
	$report_label = $CI->lang->line($lang_key);
	$report_prefix = empty($report_prefix) ? '' : $report_prefix . '_';
	// no summary nor detailed reports for receivings
	if(!empty($report_label) && $report_label != $lang_key . ' (TBD)')
	{
		?>
			<a class="list-group-item" href="<?php echo site_url('reports/' . $report_prefix . preg_replace('/reports_(.*)/', '$1', $report_name));?>"><?php echo $report_label; ?></a>
		<?php 
	}
}

?>