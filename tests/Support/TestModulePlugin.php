<?php

namespace Tests\Support;

use App\Libraries\Plugins\BasePlugin;

/**
 * Fixture plugin for module registration tests.
 *
 * Exposes the protected registration helpers on BasePlugin and silences
 * plugin file logging — unit tests report failures through PHPUnit.
 */
class TestModulePlugin extends BasePlugin
{
    public function getPluginId(): string
    {
        return 'testmodule';
    }

    public function getPluginName(): string
    {
        return 'Test Module Plugin';
    }

    public function getPluginDescription(): string
    {
        return 'Fixture plugin for module registration tests';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function registerEvents(): void
    {
    }

    public function getConfigView(): ?string
    {
        return null;
    }

    public function install(): bool
    {
        $ok = $this->registerModule('testmodule', 500, 'office');
        return $ok && $this->registerSubPermission('testmodule_extra', 'testmodule');
    }

    public function uninstall(): bool
    {
        return true;
    }

    protected function log(string $level, string $message): void
    {
        // No-op in tests: results come from PHPUnit, not log files.
    }

    public function publicRegisterModule(string $module_id): bool
    {
        return $this->registerModule($module_id);
    }

    public function publicUnregisterModule(string $module_id): bool
    {
        return $this->unregisterModule($module_id);
    }

    public function publicRegisterSubPermission(string $permission_id, string $module_id): bool
    {
        return $this->registerSubPermission($permission_id, $module_id);
    }

    public function publicUnregisterSubPermission(string $permission_id): bool
    {
        return $this->unregisterSubPermission($permission_id);
    }
}
