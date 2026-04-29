<?php

namespace App\Libraries\Plugins;

use App\Models\PluginConfig;
use CodeIgniter\Events\Events;
use Config\Database;
use Config\Services;
use Throwable;

class PluginManager
{
    private array $plugins = [];
    private array $enabledPlugins = [];
    private PluginConfig $configModel;
    private string $pluginsPath;
    private bool $eventsRegistered = false;
    private static bool $discovered = false;
    private static array $registeredNamespaces = [];

    public function __construct()
    {
        $this->configModel = new PluginConfig();
        $this->pluginsPath = APPPATH . 'Plugins';
    }

    public function discoverPlugins(): void
    {
        if (self::$discovered) {
            log_message('debug', 'Plugin discovery already completed, skipping');
            return;
        }

        if (!is_dir($this->pluginsPath)) {
            log_message('debug', 'Plugins directory does not exist: ' . $this->pluginsPath);
            return;
        }

        $pluginDirs = glob($this->pluginsPath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);

        foreach ($pluginDirs as $pluginDir) {
            $pluginName = basename($pluginDir);
            $mainFile = $pluginDir . DIRECTORY_SEPARATOR . $pluginName . '.php';

            if (!file_exists($mainFile)) {
                continue;
            }

            $className = 'App\\Plugins\\' . $pluginName . '\\' . $pluginName;

            if (!class_exists($className)) {
                continue;
            }

            if (!is_subclass_of($className, PluginInterface::class)) {
                continue;
            }

            try {
                $plugin = new $className();
            } catch (Throwable $e) {
                log_message('error', "Failed to instantiate plugin {$className}: " . $e->getMessage());
                continue;
            }

            $this->plugins[$plugin->getPluginId()] = $plugin;

            if ($this->isPluginEnabled($plugin->getPluginId())) {
                $this->registerNamespace($plugin->getPluginId());
            }

            log_message('debug', "Discovered plugin: {$plugin->getPluginName()}");
        }

        self::$discovered = true;
        log_message('debug', 'Plugin discovery completed');
    }

    public function registerPluginEvents(): void
    {
        if ($this->eventsRegistered) {
            return;
        }

        foreach ($this->plugins as $pluginId => $plugin) {
            if ($this->isPluginEnabled($pluginId)) {
                $this->enabledPlugins[$pluginId] = $plugin;
                $plugin->registerEvents();
                log_message('debug', "Registered events for plugin: {$plugin->getPluginName()}");
            }
        }

        $this->eventsRegistered = true;
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
        $enabled = $this->configModel->getValue($this->getEnabledKey($pluginId));
        return $enabled === '1' || $enabled === 'true';
    }

    public function canLoadPlugins(): bool
    {
        $db = Database::connect();
        return $db->tableExists('plugin_config');
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
            $this->configModel->setValue($this->getInstalledKey($pluginId), '1');
        }

        $this->configModel->setValue($this->getEnabledKey($pluginId), '1');

        $this->registerNamespace($pluginId);

        log_message('info', "Plugin enabled: {$pluginId}");

        return true;
    }

    public function disablePlugin(string $pluginId): bool
    {
        if (!$this->getPlugin($pluginId)) {
            log_message('error', "Plugin not found: {$pluginId}");
            return false;
        }

        $this->configModel->setValue($this->getEnabledKey($pluginId), '0');
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
        return $this->configModel->getValue("{$pluginId}_{$key}") ?? $default;
    }

    public function setSetting(string $pluginId, string $key, mixed $value): bool
    {
        return $this->configModel->setValue("{$pluginId}_{$key}", $value);
    }

    /**
     * Registers PSR-4 namespaces for all plugin directories without touching the DB.
     * Call this early (pre_system) so CI4's module route discovery can find each
     * plugin's Config/Routes.php before the router runs.
     */
    public static function registerAllNamespaces(): void
    {
        $pluginsPath = APPPATH . 'Plugins';
        if (!is_dir($pluginsPath)) {
            return;
        }

        $loader = Services::autoloader();
        foreach (glob($pluginsPath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [] as $dir) {
            $name = basename($dir);
            $namespace = "App\\Plugins\\{$name}";
            if (!in_array($namespace, self::$registeredNamespaces, true)) {
                $loader->addNamespace($namespace, $dir . DIRECTORY_SEPARATOR);
                self::$registeredNamespaces[] = $namespace;
            }
        }
    }

    public static function resetStatic(): void
    {
        self::$discovered = false;
        self::$registeredNamespaces = [];
    }

    private function registerNamespace(string $pluginId): void
    {
        $plugin = $this->plugins[$pluginId] ?? null;
        if ($plugin === null) {
            return;
        }

        // Derive the directory name from the class: App\Plugins\MailchimpPlugin\MailchimpPlugin → MailchimpPlugin
        // Single-file plugins have only 3 segments (App\Plugins\ClassName) and have no subdirectory.
        $parts = explode('\\', get_class($plugin));
        if (count($parts) < 4) {
            return;
        }

        $pluginDirName = $parts[2];
        $namespace = "App\\Plugins\\{$pluginDirName}";

        if (!in_array($namespace, self::$registeredNamespaces, true)) {
            $loader = Services::autoloader();
            $loader->addNamespace($namespace, APPPATH . "Plugins/{$pluginDirName}");
            self::$registeredNamespaces[] = $namespace;
            log_message('debug', "Registered namespace for plugin dir: {$pluginDirName}");
        }
    }

    private function getEnabledKey(string $pluginId): string
    {
        return "{$pluginId}__enabled";
    }

    private function getInstalledKey(string $pluginId): string
    {
        return "{$pluginId}__installed";
    }
}
