<?php

use App\Models\Employee;

/**
 * Report helper
 *
 * @param string $report_prefix
 * @param string $report_name
 * @param int $person_id
 * @param string $permission_id
 *
 * @property employee $employee
 */
function show_report_if_allowed(string $report_prefix, string $report_name, int $person_id, string $permission_id = ''): void
{
	$permission_id = empty($permission_id) ? $report_name : $permission_id;    //TODO: Use String Interpolation here.
	$employee = model(Employee::class);

	if($employee->has_grant($permission_id, $person_id))
	{
		show_report($report_prefix, $report_name, $permission_id);
	}
}

function show_report(string $report_prefix, string $report_name, string $lang_key = ''): void
{
	if(empty($lang_key))
	{
		$lang_key = str_replace('reports_','', $report_name);
		$report_label = lang('Reports.' . $lang_key);
	}
	$report_label = lang('Reports.' . $lang_key);

	$report_prefix = empty($report_prefix) ? '' : $report_prefix . '_';

	// no summary nor detailed reports for receivings
	if(!empty($report_label) && $report_label != $lang_key . ' (TBD)')	//TODO: String Interpolation.  Also !==
	{//TODO: Is there a better way to do this?  breaking the php like this makes it more difficult to read.
		?>
			<a class="list-group-item" href="<?= "reports/$report_prefix" . preg_replace('/reports_(.*)/', '$1', $report_name) ?>"><?= $report_label; ?></a>
		<?php
	}
}
?>
