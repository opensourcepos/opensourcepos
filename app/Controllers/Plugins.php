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
        $data['table_headers'] = get_plugin_manage_table_headers();
        return view('plugins/manage', $data);
    }

    public function getSearch(): ResponseInterface
    {
        $search = strtolower($this->request->getGet('search') ?? '');
        $limit  = (int)($this->request->getGet('limit') ?? 0);
        $offset = (int)($this->request->getGet('offset') ?? 0);
        $sort   = $this->request->getGet('sort', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'name';
        $order  = strtolower($this->request->getGet('order', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'asc');

        $pluginData = $this->buildPluginDataArray();

        if ($search !== '') {
            $pluginData = array_values(array_filter($pluginData, static function (array $p) use ($search): bool {
                return str_contains(strtolower($p['name']), $search)
                    || str_contains(strtolower($p['description']), $search)
                    || str_contains(strtolower($p['id']), $search);
            }));
        }

        $total = count($pluginData);

        usort($pluginData, static function (array $a, array $b) use ($sort, $order): int {
            $valA = strtolower($a[$sort] ?? $a['name']);
            $valB = strtolower($b[$sort] ?? $b['name']);
            return $order === 'asc' ? strcmp($valA, $valB) : strcmp($valB, $valA);
        });

        $pluginData = $limit > 0 ? array_slice($pluginData, $offset, $limit) : array_slice($pluginData, $offset);

        return $this->response->setJSON(['total' => $total, 'rows' => array_map('get_plugin_data_row', $pluginData)]);
    }

    public function getRow(string $pluginId): ResponseInterface
    {
        $plugin = $this->pluginManager->getPlugin($pluginId);
        if (!$plugin) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Plugins.not_found')]);
        }

        $enabled = $this->pluginManager->getEnabledPlugins();
        $pluginData = [
            'id'          => $plugin->getPluginId(),
            'name'        => $plugin->getPluginName(),
            'description' => $plugin->getPluginDescription(),
            'version'     => $plugin->getVersion(),
            'enabled'     => isset($enabled[$pluginId]),
            'has_config'  => $plugin->getConfigView() !== null,
        ];

        return $this->response->setJSON(get_plugin_data_row($pluginData));
    }

    private function buildPluginDataArray(): array
    {
        $plugins = $this->pluginManager->getAllPlugins();
        $enabled = $this->pluginManager->getEnabledPlugins();
        $result  = [];

        foreach ($plugins as $pluginId => $plugin) {
            $result[] = [
                'id'          => $plugin->getPluginId(),
                'name'        => $plugin->getPluginName(),
                'description' => $plugin->getPluginDescription(),
                'version'     => $plugin->getVersion(),
                'enabled'     => isset($enabled[$pluginId]),
                'has_config'  => $plugin->getConfigView() !== null,
            ];
        }

        return $result;
    }

    public function postEnable(string $pluginId): ResponseInterface
    {
        if ($this->pluginManager->enablePlugin($pluginId)) {
            return $this->response->setJSON(['success' => true, 'message' => lang('Plugins.enabled')]);
        }
        return $this->response->setJSON(['success' => false, 'message' => lang('Plugins.enable_failed')]);
    }

    public function postDisable(string $pluginId): ResponseInterface
    {
        if ($this->pluginManager->disablePlugin($pluginId)) {
            return $this->response->setJSON(['success' => true, 'message' => lang('Plugins.disabled')]);
        }
        return $this->response->setJSON(['success' => false, 'message' => lang('Plugins.disable_failed')]);
    }

    public function postUninstall(string $pluginId): ResponseInterface
    {
        if ($this->pluginManager->uninstallPlugin($pluginId)) {
            return $this->response->setJSON(['success' => true, 'message' => lang('Plugins.uninstalled')]);
        }
        return $this->response->setJSON(['success' => false, 'message' => lang('Plugins.uninstall_failed')]);
    }

    public function getConfig(string $pluginId): ResponseInterface
    {
        $plugin = $this->pluginManager->getPlugin($pluginId);

        if (!$plugin) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Plugins.not_found')]);
        }

        $configView = $plugin->getConfigView();
        if (!$configView) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Plugins.no_config')]);
        }

        $settings = $plugin->getSettings();
        echo view($configView, ['settings' => $settings, 'plugin' => $plugin]);
        return $this->response;
    }

    public function postSaveConfig(string $pluginId): ResponseInterface
    {
        $plugin = $this->pluginManager->getPlugin($pluginId);

        if (!$plugin) {
            return $this->response->setJSON(['success' => false, 'message' => lang('Plugins.not_found')]);
        }

        $settings = $this->request->getPost();
        unset($settings['_method'], $settings[csrf_token()]);

        if ($plugin->saveSettings($settings)) {
            return $this->response->setJSON(['success' => true, 'message' => lang('Plugins.settings_saved')]);
        }
        return $this->response->setJSON(['success' => false, 'message' => lang('Plugins.settings_save_failed')]);
    }
}
