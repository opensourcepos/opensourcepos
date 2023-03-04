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
function show_report_if_allowed(string $report_prefix, string $report_name, int $person_id, string $lang_key = ''): void
{
	/**
	 * In grants database, for reports, permission_id == reports_name
	 * lang_key = Reports.customers
	 * permission_id = reports_customers
	 */

	$employee = model(Employee::class);
	$permission_id = 'reports_' . $report_name;
	if (empty($lang_key))
	{
		$lang_key = str_replace('_','.',ucfirst($permission_id));
	}

	if($employee->has_grant($permission_id, $person_id))
	{
		show_report($report_prefix, $report_name, $lang_key);
	}
}

/**
 * @param string $report_name
 * @param string $report_prefix
 * @param string $lang_key
 * @return array
 */
function get_report_link(string $report_name, string $report_prefix = '', string $lang_key = ''): array
{
	$path = 'reports/';
	if ($report_prefix !== '') {
		$path .= $report_prefix . '_';
	}

	/**
	 * Sanitize the report name in case it has come from the permissions table.
	 */
	$report_name = str_replace('reports_','',$report_name);
	$path .= $report_name;

	if ($lang_key === '') {
		$lang_key = 'Reports.' . $report_name;
	}

	return [
		'path' => site_url($path),
		'label' => lang($lang_key),
	];
}

/**
 * @param string $permission_id
 * @param string[] $restrict_views
 * @return bool
 */
function can_show_report($permission_id, array $restrict_views = [])
{
	if (strpos($permission_id, 'reports_') === false) {
		return false;
	}

	foreach ($restrict_views as $restrict_view) {
		if (strpos($permission_id, $restrict_view) !== false) {
			return false;
		}
	}

	return true;
}
function show_report(string $report_prefix, string $report_name, string $lang_key = ''): void
{
	/**
	 * show_report does two things.
	 * 1. Shows a link to the report.
	 * 2. Displays a localized label for the link.
	 */
	$lang_key = empty($lang_key) ? $report_name : $lang_key;
	$report_label = lang($lang_key);
	$report_prefix = empty($report_prefix) ? '' : $report_prefix . '.';

	// no summary nor detailed reports for receivings
	if(!empty($report_label) && $report_label != $lang_key . ' (TBD)')	//TODO: String Interpolation.  Also !==
	{//TODO: Is there a better way to do this?  breaking the php like this makes it more difficult to read.
		?>
			<a class="list-group-item" href="<?= site_url("reports/$report_prefix" . preg_replace('/reports_(.*)/', '$1', $report_name)) ?>"><?= $report_label; ?></a>
		<?php
	}
}
?>
