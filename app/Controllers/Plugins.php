<?php

namespace App\Controllers;

use App\Libraries\Plugins\PluginManager;
use CodeIgniter\HTTP\ResponseInterface;

class Plugins extends Secure_Controller
{
    private PluginManager $pluginManager;

    public function __construct()
    {
        parent::__construct('plugins');
        $this->pluginManager = service('pluginManager');
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
            return $this->response->setJSON(['success' => true, 'message' => lang('Plugins.plugin_enabled')]);
        }
        return $this->response->setJSON(['success' => false, 'message' => lang('Plugins.plugin_enable_failed')]);
    }

    public function postDisable(string $pluginId): ResponseInterface
    {
        if ($this->pluginManager->disablePlugin($pluginId)) {
            return $this->response->setJSON(['success' => true, 'message' => lang('Plugins.plugin_disabled')]);
        }
        return $this->response->setJSON(['success' => false, 'message' => lang('Plugins.plugin_disable_failed')]);
    }

    public function postUninstall(string $pluginId): ResponseInterface
    {
        if ($this->pluginManager->uninstallPlugin($pluginId)) {
            return $this->response->setJSON(['success' => true, 'message' => lang('Plugins.plugin_uninstalled')]);
        }
        return $this->response->setJSON(['success' => false, 'message' => lang('Plugins.plugin_uninstall_failed')]);
    }

    public function getConfig(string $pluginId): ResponseInterface
    {
        $plugin = $this->pluginManager->getPlugin($pluginId);

        if (!$plugin) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Plugins.plugin_not_found')]);
        }

        $configView = $plugin->getConfigView();
        if (!$configView) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Plugins.plugin_no_config')]);
        }

        $settings = $plugin->getSettings();
        echo view($configView, ['settings' => $settings, 'plugin' => $plugin]);
        return $this->response;
    }

    public function postSaveConfig(string $pluginId): ResponseInterface
    {
        $plugin = $this->pluginManager->getPlugin($pluginId);

        if (!$plugin) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Plugins.plugin_not_found')]);
        }

        $settings = $this->request->getPost();
        unset($settings['_method'], $settings[csrf_token()]);

        if ($plugin->saveSettings($settings)) {
            return $this->response->setJSON(['success' => true, 'message' => lang('Plugins.settings_saved')]);
        }
        return $this->response->setJSON(['success' => false, 'message' => lang('Plugins.settings_save_failed')]);
    }
}
