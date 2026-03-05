<?php

namespace App\Libraries\Plugins;

use App\Models\PluginConfigModel;
use CodeIgniter\Events\Events;
use DirectoryIterator;

/**
 * Plugin Manager
 * 
 * Discovers, loads, and manages plugins in the OSPOS system.
 * Plugins are discovered from app/Plugins directory and must implement PluginInterface.
 */
class PluginManager
{
    /**
     * Array of discovered plugin instances.
     */
    private array $plugins = [];

    /**
     * Array of enabled plugin instances.
     */
    private array $enabledPlugins = [];

    /**
     * Plugin configuration model.
     */
    private PluginConfigModel $configModel;

    /**
     * Path to plugins directory.
     */
    private string $pluginsPath;

    /**
     * Initialize the plugin manager.
     */
    public function __construct()
    {
        $this->configModel = new PluginConfigModel();
        $this->pluginsPath = APPPATH . 'Plugins';
    }

    /**
     * Discover and load all available plugins.
     * 
     * Scans the Plugins directory for classes implementing PluginInterface.
     */
    public function discoverPlugins(): void
    {
        if (!is_dir($this->pluginsPath)) {
            log_message('debug', 'Plugins directory does not exist: ' . $this->pluginsPath);
            return;
        }

        $iterator = new DirectoryIterator($this->pluginsPath);
        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $className = 'App\\Plugins\\' . $file->getBasename('.php');
            
            if (!class_exists($className)) {
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

    /**
     * Register event listeners for all enabled plugins.
     * 
     * This should be called during application bootstrap.
     */
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

    /**
     * Get all discovered plugins.
     * 
     * @return array<string, PluginInterface>
     */
    public function getAllPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * Get all enabled plugins.
     * 
     * @return array<string, PluginInterface>
     */
    public function getEnabledPlugins(): array
    {
        return $this->enabledPlugins;
    }

    /**
     * Get a specific plugin by ID.
     */
    public function getPlugin(string $pluginId): ?PluginInterface
    {
        return $this->plugins[$pluginId] ?? null;
    }

    /**
     * Check if a plugin is enabled.
     */
    public function isPluginEnabled(string $pluginId): bool
    {
        $enabled = $this->configModel->get($this->getEnabledKey($pluginId));
        return $enabled === '1' || $enabled === 'true';
    }

    /**
     * Enable a plugin.
     */
    public function enablePlugin(string $pluginId): bool
    {
        $plugin = $this->getPlugin($pluginId);
        if (!$plugin) {
            log_message('error', "Plugin not found: {$pluginId}");
            return false;
        }

        // Check if plugin needs installation
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

    /**
     * Disable a plugin.
     */
    public function disablePlugin(string $pluginId): bool
    {
        $this->configModel->set($this->getEnabledKey($pluginId), '0');
        log_message('info', "Plugin disabled: {$pluginId}");
        
        return true;
    }

    /**
     * Uninstall a plugin completely.
     */
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

        // Remove all plugin configuration
        $this->configModel->deleteAllStartingWith($pluginId . '_');
        
        return true;
    }

    /**
     * Get plugin setting.
     */
    public function getSetting(string $pluginId, string $key, mixed $default = null): mixed
    {
        return $this->configModel->get("{$pluginId}_{$key}") ?? $default;
    }

    /**
     * Set plugin setting.
     */
    public function setSetting(string $pluginId, string $key, mixed $value): bool
    {
        return $this->configModel->set("{$pluginId}_{$key}", $value);
    }

    /**
     * Get the enabled config key for a plugin.
     */
    private function getEnabledKey(string $pluginId): string
    {
        return "{$pluginId}_enabled";
    }

    /**
     * Get the installed config key for a plugin.
     */
    private function getInstalledKey(string $pluginId): string
    {
        return "{$pluginId}_installed";
    }
}