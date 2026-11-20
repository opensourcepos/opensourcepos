<?php

namespace App\Libraries\Plugins;

use App\Models\PluginConfig;

abstract class BasePlugin implements PluginInterface
{
    protected PluginConfig $configModel;

    public function __construct()
    {
        $this->configModel = new PluginConfig();
    }

    public function install(): bool
    {
        return true;
    }

    public function uninstall(): bool
    {
        return true;
    }

    public function isEnabled(): bool
    {
        $enabled = $this->configModel->getValue($this->getPluginId(), 'enabled');
        return $enabled === '1' || $enabled === 'true';
    }

    protected function getSetting(string $key, mixed $default = null): mixed
    {
        $value = $this->configModel->getValue($this->getPluginId(), $key);
        return $value ?? $default;
    }

    protected function setSetting(string $key, mixed $value): bool
    {
        $stringValue = is_array($value) || is_object($value)
            ? json_encode($value)
            : (string)$value;

        return $this->configModel->setValue($this->getPluginId(), $key, $stringValue);
    }

    public function getSettings(): array
    {
        return $this->configModel->getPluginSettings($this->getPluginId());
    }

    public function saveSettings(array $settings): bool
    {
        $normalized = [];
        foreach ($settings as $key => $value) {
            $normalized[$key] = is_array($value) || is_object($value)
                ? json_encode($value)
                : (string)$value;
        }

        return $this->configModel->batchSave($this->getPluginId(), $normalized);
    }

    public function getConfigViewData(): array
    {
        return [];
    }

    protected function log(string $level, string $message): void
    {
        log_plugin_message($level, $message, $this->getPluginId());
    }

    protected function logTo(string $level, string $message, string $logName): void
    {
        log_plugin_message($level, $message, $this->getPluginId(), $logName);
    }

    protected function renderView(string $viewName, array $data = []): string
    {
        $namespace = substr(get_class($this), 0, strrpos(get_class($this), '\\'));
        return view($namespace . '\\Views\\' . $viewName, $data);
    }

    /**
     * Module and permission ids must be the plugin id itself or prefixed with
     * '{plugin_id}_' (e.g. 'mailchimp' or 'mailchimp_dashboard'). PluginManager
     * relies on this naming to auto-unregister everything on uninstall.
     */
    private function idMatchesPluginConvention(string $id): bool
    {
        $pluginId = $this->getPluginId();
        return $id === $pluginId || str_starts_with($id, $pluginId . '_');
    }

    /**
     * Register a module in the permission system and auto-grant it to admin (person_id=1).
     * Call from install(). Idempotent — safe to call multiple times.
     *
     * PluginManager automatically unregisters the module when the plugin is
     * uninstalled, matched by the required id convention (see idMatchesPluginConvention).
     * Language keys resolved from plugin's Language/{locale}/Module.php file.
     */
    protected function registerModule(
        string $module_id,
        int $sort = 500,
        string $admin_menu_group = 'office'
    ): bool {
        if (!$this->idMatchesPluginConvention($module_id)) {
            $this->log('error', "Module id '{$module_id}' must be '{$this->getPluginId()}' or prefixed with '{$this->getPluginId()}_'");
            return false;
        }
        $db = \Config\Database::connect();
        $db->table('modules')->ignore(true)->insert([
            'module_id'     => $module_id,
            'name_lang_key' => 'module_' . $module_id,
            'desc_lang_key' => 'module_' . $module_id . '_desc',
            'sort'          => $sort,
        ]);
        $db->table('permissions')->ignore(true)->insert([
            'permission_id' => $module_id,
            'module_id'     => $module_id,
        ]);
        $db->table('grants')->ignore(true)->insert([
            'permission_id' => $module_id,
            'person_id'     => 1,
            'menu_group'    => $admin_menu_group,
        ]);
        return true;
    }

    /**
     * Remove a module and all its permissions from the permission system.
     * Grants cascade automatically via FK.
     *
     * Calling this from uninstall() is not required — PluginManager
     * auto-unregisters modules matching the plugin id convention on uninstall.
     * Use this only to remove a module while the plugin stays installed.
     *
     * Note: ospos_permissions has no FK cascade from ospos_modules, so
     * permissions must be deleted before the module row.
     */
    protected function unregisterModule(string $module_id): bool
    {
        $db = \Config\Database::connect();
        $db->table('permissions')->where('module_id', $module_id)->delete();
        $db->table('modules')->where('module_id', $module_id)->delete();
        return true;
    }

    /**
     * Register a sub-permission under an existing module and auto-grant to admin.
     * Sub-permissions use menu_group '--' per core convention.
     * Call from install() after registerModule().
     */
    protected function registerSubPermission(string $permission_id, string $module_id): bool
    {
        if (!$this->idMatchesPluginConvention($permission_id)) {
            $this->log('error', "Permission id '{$permission_id}' must be '{$this->getPluginId()}' or prefixed with '{$this->getPluginId()}_'");
            return false;
        }
        $db = \Config\Database::connect();
        $db->table('permissions')->ignore(true)->insert([
            'permission_id' => $permission_id,
            'module_id'     => $module_id,
        ]);
        $db->table('grants')->ignore(true)->insert([
            'permission_id' => $permission_id,
            'person_id'     => 1,
            'menu_group'    => '--',
        ]);
        return true;
    }

    /**
     * Remove a specific sub-permission. Grants cascade automatically via FK.
     * unregisterModule() already handles this for all sub-permissions under a module,
     * so only call this directly when removing a sub-permission independently.
     */
    protected function unregisterSubPermission(string $permission_id): bool
    {
        \Config\Database::connect()->table('permissions')
            ->where('permission_id', $permission_id)->delete();
        return true;
    }
}
