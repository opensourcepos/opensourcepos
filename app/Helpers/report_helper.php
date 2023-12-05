<?php

/**
 * @param string $report_name
 * @param string $report_prefix
 * @param string $lang_key
 * @return array
 */
function get_report_link(string $report_name, string $report_prefix = '', string $lang_key = ''): array
{
    $path = 'reports/';
    if ($report_prefix !== '')
	{
        $path .= $report_prefix . '_';
    }

    /**
     * Sanitize the report name in case it has come from the permissions table.
     */
    $report_name = str_replace('reports_', '', $report_name);
    $path .= $report_name;

    if ($lang_key === '')
	{
        $lang_key = 'Reports.' . $report_name;
    }

    return [
        'path'  => site_url($path),
        'label' => lang($lang_key),
    ];
}

/**
 * @param string   $permission_id
 * @param string[] $restrict_views
 *
 * @return bool
 */
function can_show_report($permission_id, array $restrict_views = []): bool
{
    if (!strpos($permission_id, 'reports_'))
  {
        return false;
    }

    foreach ($restrict_views as $restrict_view)
	{
        if (strpos($permission_id, $restrict_view) !== false)
		{
            return false;
        }
    }

    return true;
}
