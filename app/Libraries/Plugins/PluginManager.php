<?php

namespace App\Libraries\Plugins;

use App\Models\PluginConfig;
use App\Models\PluginMigrationModel;
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

        $this->runPendingMigrations();

        foreach ($this->plugins as $pluginId => $plugin) {
            if ($this->isPluginEnabled($pluginId)) {
                $this->enabledPlugins[$pluginId] = $plugin;
                $plugin->registerEvents();
                log_message('debug', "Registered events for plugin: {$plugin->getPluginName()}");
            }
        }

        $this->eventsRegistered = true;
    }

    private function runPendingMigrations(): void
    {
        if (session()->get('plugin_migrations_ran')) {
            return;
        }

        $db = Database::connect();

        if (!$db->tableExists('plugin_migrations')) {
            return;
        }

        $migrationModel = new PluginMigrationModel();
        $forge = Database::forge();

        foreach ($this->plugins as $pluginId => $plugin) {
            if (!$this->isPluginEnabled($pluginId)) {
                continue;
            }

            $parts = explode('\\', get_class($plugin));
            if (count($parts) < 4) {
                continue;
            }

            $pluginDirName = $parts[2];
            $migrationsPath = APPPATH . "Plugins/{$pluginDirName}/Migrations/";

            if (!is_dir($migrationsPath)) {
                continue;
            }

            $files = glob($migrationsPath . '*.php') ?: [];
            $migrationFiles = array_filter($files, static fn($f) => preg_match('/\/\d{14}_/', $f));
            sort($migrationFiles);

            $currentVersion = $migrationModel->getVersion($pluginId);

            foreach ($migrationFiles as $file) {
                $basename = basename($file, '.php');
                $timestamp = (int) substr($basename, 0, 14);

                if ($timestamp <= $currentVersion) {
                    continue;
                }

                $className = substr($basename, 15); // strip "20260627120000_"
                $fqcn = "App\\Plugins\\{$pluginDirName}\\Migrations\\{$className}";

                require_once $file;

                if (!class_exists($fqcn)) {
                    log_message('error', "Plugin migration class not found: {$fqcn}");
                    break;
                }

                try {
                    (new $fqcn($db, $forge))->up();
                    $migrationModel->setVersion($pluginId, $timestamp);
                    log_message('info', "Plugin migration ran: {$pluginId} v{$timestamp}");
                } catch (Throwable $e) {
                    log_message('error', "Plugin migration failed: {$pluginId} v{$timestamp}: " . $e->getMessage());
                    break;
                }
            }
        }

        session()->set('plugin_migrations_ran', true);
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
        $enabled = $this->configModel->getValue($pluginId, 'enabled');
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

        if (!$this->configModel->exists($pluginId, 'installed')) {
            if (!$plugin->install()) {
                log_message('error', "Failed to install plugin: {$pluginId}");
                return false;
            }
            $this->configModel->setValue($pluginId, 'installed', '1', true);
        }

        $this->configModel->setValue($pluginId, 'enabled', '1', true);

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

        $this->configModel->setValue($pluginId, 'enabled', '0', true);
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

        $this->disablePlugin($pluginId);

        if (!$plugin->uninstall()) {
            log_message('error', "Failed to uninstall plugin: {$pluginId}");
            return false;
        }

        $this->configModel->deleteAllForPlugin($pluginId);

        return true;
    }

    public function getSetting(string $pluginId, string $key, mixed $default = null): mixed
    {
        return $this->configModel->getValue($pluginId, $key) ?? $default;
    }

    public function setSetting(string $pluginId, string $key, mixed $value): bool
    {
        return $this->configModel->setValue($pluginId, $key, $value);
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
}
