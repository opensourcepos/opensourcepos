<?php

namespace App\Libraries\Plugins;

use App\Models\PluginConfig;
use CodeIgniter\Events\Events;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class PluginManager
{
    private array $plugins = [];
    private array $enabledPlugins = [];
    private PluginConfig $configModel;
    private string $pluginsPath;

    public function __construct()
    {
        $this->configModel = new PluginConfig();
        $this->pluginsPath = APPPATH . 'Plugins';
    }

    public function discoverPlugins(): void
    {
        if (!is_dir($this->pluginsPath)) {
            log_message('debug', 'Plugins directory does not exist: ' . $this->pluginsPath);
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->pluginsPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $className = $this->getClassNameFromFile($file->getPathname());
            
            if (!$className || !class_exists($className)) {
                continue;
            }

            $plugin = new $className();

            if (!$plugin instanceof PluginInterface) {
                log_message('warning', "Plugin {$className} does not implement PluginInterface");
                continue;
            }

            $this->plugins[$plugin->getPluginId()] = $plugin;
            log_message('debug', "Discovered plugin: {$plugin->getPluginName()}");
        }
    }

    private function getClassNameFromFile(string $pathname): ?string
    {
        $relativePath = str_replace($this->pluginsPath . DIRECTORY_SEPARATOR, '', $pathname);
        $relativePath = str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);
        $className = 'App\\Plugins\\' . str_replace('.php', '', $relativePath);
        
        return $className;
    }

    public function registerPluginEvents(): void
    {
        foreach ($this->plugins as $pluginId => $plugin) {
            if ($this->isPluginEnabled($pluginId)) {
                $this->enabledPlugins[$pluginId] = $plugin;
                $plugin->registerEvents();
                log_message('debug', "Registered events for plugin: {$plugin->getPluginName()}");
            }
        }
    }

    public function getAllPlugins(): array
    {
        return $this->plugins;
    }

    public function getEnabledPlugins(): array
    {
        return $this->enabledPlugins;
    }

    public function getPlugin(string $pluginId): ?PluginInterface
    {
        return $this->plugins[$pluginId] ?? null;
    }

    public function isPluginEnabled(string $pluginId): bool
    {
        $enabled = $this->configModel->get($this->getEnabledKey($pluginId));
        return $enabled === '1' || $enabled === 'true';
    }

    public function enablePlugin(string $pluginId): bool
    {
        $plugin = $this->getPlugin($pluginId);
        if (!$plugin) {
            log_message('error', "Plugin not found: {$pluginId}");
            return false;
        }

        if (!$this->configModel->exists($this->getInstalledKey($pluginId))) {
            if (!$plugin->install()) {
                log_message('error', "Failed to install plugin: {$pluginId}");
                return false;
            }
            $this->configModel->set($this->getInstalledKey($pluginId), '1');
        }

        $this->configModel->set($this->getEnabledKey($pluginId), '1');
        log_message('info', "Plugin enabled: {$pluginId}");
        
        return true;
    }

    public function disablePlugin(string $pluginId): bool
    {
        $this->configModel->set($this->getEnabledKey($pluginId), '0');
        log_message('info', "Plugin disabled: {$pluginId}");
        
        return true;
    }

    public function uninstallPlugin(string $pluginId): bool
    {
        $plugin = $this->getPlugin($pluginId);
        if (!$plugin) {
            log_message('error', "Plugin not found: {$pluginId}");
            return false;
        }

        if (!$plugin->uninstall()) {
            log_message('error', "Failed to uninstall plugin: {$pluginId}");
            return false;
        }

        $this->configModel->deleteAllStartingWith($pluginId . '_');
        
        return true;
    }

    public function getSetting(string $pluginId, string $key, mixed $default = null): mixed
    {
        return $this->configModel->get("{$pluginId}_{$key}") ?? $default;
    }

    public function setSetting(string $pluginId, string $key, mixed $value): bool
    {
        return $this->configModel->set("{$pluginId}_{$key}", $value);
    }

    private function getEnabledKey(string $pluginId): string
    {
        return "{$pluginId}_enabled";
    }

    private function getInstalledKey(string $pluginId): string
    {
        return "{$pluginId}_installed";
    }
}