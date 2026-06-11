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
        log_plugin_message($this->getPluginId(), $level, $message);
    }

    protected function logTo(string $logName, string $level, string $message): void
    {
        log_plugin_message($this->getPluginId(), $level, $message, $logName);
    }

    protected function renderView(string $viewName, array $data = []): string
    {
        $namespace = substr(get_class($this), 0, strrpos(get_class($this), '\\'));
        return view($namespace . '\\Views\\' . $viewName, $data);
    }
}
