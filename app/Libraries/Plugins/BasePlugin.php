<?php

namespace App\Libraries\Plugins;

use App\Models\Plugin_config;

/**
 * Base Plugin Class
 * 
 * Abstract base class providing common plugin functionality.
 * Plugins can extend this class to reduce boilerplate code.
 */
abstract class BasePlugin implements PluginInterface
{
    protected Plugin_config $configModel;

    public function __construct()
    {
        $this->configModel = new Plugin_config();
    }

    /**
     * Default install implementation.
     * Override in subclass for custom installation logic.
     */
    public function install(): bool
    {
        return true;
    }

    /**
     * Default uninstall implementation.
     * Override in subclass for custom uninstallation logic.
     */
    public function uninstall(): bool
    {
        return true;
    }

    /**
     * Check if the plugin is enabled.
     */
    public function isEnabled(): bool
    {
        $enabled = $this->configModel->get("{$this->getPluginId()}_enabled");
        return $enabled === '1' || $enabled === 'true';
    }

    /**
     * Get a plugin setting.
     */
    protected function getSetting(string $key, mixed $default = null): mixed
    {
        $value = $this->configModel->get("{$this->getPluginId()}_{$key}");
        return $value ?? $default;
    }

    /**
     * Set a plugin setting.
     */
    protected function setSetting(string $key, mixed $value): bool
    {
        $stringValue = is_array($value) || is_object($value) 
            ? json_encode($value) 
            : (string)$value;
            
        return $this->configModel->set("{$this->getPluginId()}_{$key}", $stringValue);
    }

    /**
     * Get all plugin settings.
     */
    public function getSettings(): array
    {
        return $this->configModel->getPluginSettings($this->getPluginId());
    }

    /**
     * Save plugin settings.
     */
    public function saveSettings(array $settings): bool
    {
        $prefixedSettings = [];
        foreach ($settings as $key => $value) {
            $prefixedSettings["{$this->getPluginId()}_{$key}"] = (string)$value;
        }
        
        return $this->configModel->batchSave($prefixedSettings);
    }

    /**
     * Log a plugin-specific message.
     */
    protected function log(string $level, string $message): void
    {
        log_message($level, "[Plugin:{$this->getPluginName()}] {$message}");
    }
}