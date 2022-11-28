<?php
/**
 * @var string $module_name
 */
echo lang('Errors.no_permission_module') . " $module_name" . (!empty($permission_id) ? " ($permission_id)" : '');