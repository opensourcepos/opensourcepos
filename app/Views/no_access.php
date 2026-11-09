<?php
/**
 * @var string $module_name
 * @var string $permission_id
 */
?>
<?= lang('Error.no_permission_module') . ' ' . esc($module_name) . (! empty($permission_id) ? ' (' . esc($permission_id) . ')' : '') ?>
<script>document.getElementById('submit')?.setAttribute('disabled', 'disabled');</script>
