<?php

namespace App\Controllers\Plugins;

use App\Controllers\Secure_Controller;
use App\Libraries\Plugins\PluginManager;
use CodeIgniter\HTTP\ResponseInterface;

class Manage extends Secure_Controller
{
    private PluginManager $pluginManager;

    public function __construct()
    {
        parent::__construct('plugins');
        $this->pluginManager = new PluginManager();
        $this->pluginManager->discoverPlugins();
    }

    public function getIndex(): string
    {
        $plugins = $this->pluginManager->getAllPlugins();
        $enabledPlugins = $this->pluginManager->getEnabledPlugins();

        $pluginData = [];
        foreach ($plugins as $pluginId => $plugin) {
            $pluginData[$pluginId] = [
                'id' => $plugin->getPluginId(),
                'name' => $plugin->getPluginName(),
                'description' => $plugin->getPluginDescription(),
                'version' => $plugin->getVersion(),
                'enabled' => isset($enabledPlugins[$pluginId]),
                'has_config' => $plugin->getConfigView() !== null,
            ];
        }

        echo view('plugins/manage', ['plugins' => $pluginData]);
        return '';
    }

    public function postEnable(string $pluginId): ResponseInterface
    {
        if ($this->pluginManager->enablePlugin($pluginId)) {
            echo json_encode(['success' => true, 'message' => lang('Plugins.plugin_enabled')]);
        } else {
            echo json_encode(['success' => false, 'message' => lang('Plugins.plugin_enable_failed')]);
        }
        return $this->response;
    }

    public function postDisable(string $pluginId): ResponseInterface
    {
        if ($this->pluginManager->disablePlugin($pluginId)) {
            echo json_encode(['success' => true, 'message' => lang('Plugins.plugin_disabled')]);
        } else {
            echo json_encode(['success' => false, 'message' => lang('Plugins.plugin_disable_failed')]);
        }
        return $this->response;
    }

    public function postUninstall(string $pluginId): ResponseInterface
    {
        if ($this->pluginManager->uninstallPlugin($pluginId)) {
            echo json_encode(['success' => true, 'message' => lang('Plugins.plugin_uninstalled')]);
        } else {
            echo json_encode(['success' => false, 'message' => lang('Plugins.plugin_uninstall_failed')]);
        }
        return $this->response;
    }

    public function getConfig(string $pluginId): ResponseInterface
    {
        $plugin = $this->pluginManager->getPlugin($pluginId);
        
        if (!$plugin) {
            echo json_encode(['success' => false, 'message' => lang('Plugins.plugin_not_found')]);
            return $this->response;
        }

        $configView = $plugin->getConfigView();
        if (!$configView) {
            echo json_encode(['success' => false, 'message' => lang('Plugins.plugin_no_config')]);
            return $this->response;
        }

        $settings = $plugin->getSettings();
        echo view($configView, ['settings' => $settings, 'plugin' => $plugin]);
        return $this->response;
    }

    public function postSaveConfig(string $pluginId): ResponseInterface
    {
        $plugin = $this->pluginManager->getPlugin($pluginId);
        
        if (!$plugin) {
            echo json_encode(['success' => false, 'message' => lang('Plugins.plugin_not_found')]);
            return $this->response;
        }

        $settings = $this->request->getPost();
        unset($settings['_method'], $settings['csrf_token_name']);

        if ($plugin->saveSettings($settings)) {
            echo json_encode(['success' => true, 'message' => lang('Plugins.settings_saved')]);
        } else {
            echo json_encode(['success' => false, 'message' => lang('Plugins.settings_save_failed')]);
        }
        
        return $this->response;
    }
}