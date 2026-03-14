<?php

namespace App\Plugins;

use App\Libraries\Plugins\BasePlugin;
use CodeIgniter\Events\Events;

/**
 * Example Plugin
 * 
 * A simple example plugin demonstrating the plugin system.
 * Logs events to the debug log for demonstration purposes.
 */
class ExamplePlugin extends BasePlugin
{
    public function getPluginId(): string
    {
        return 'example';
    }

    public function getPluginName(): string
    {
        return 'Example Plugin';
    }

    public function getPluginDescription(): string
    {
        return 'A demonstration plugin that logs OSPOS events. This plugin shows how plugins can integrate with the system.';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function registerEvents(): void
    {
        Events::on('item_change', [$this, 'onItemChange']);
        Events::on('item_sale', [$this, 'onItemSale']);
        
        $this->log('debug', 'Example plugin events registered');
    }

    public function install(): bool
    {
        $this->log('info', 'Installing Example Plugin');
        
        $this->setSetting('log_changes', '1');
        $this->setSetting('log_sales', '1');
        
        return true;
    }

    public function uninstall(): bool
    {
        $this->log('info', 'Uninstalling Example Plugin');
        return true;
    }

    public function getConfigView(): ?string
    {
        return 'Plugins/example/config';
    }

    public function getSettings(): array
    {
        return [
            'log_changes' => $this->getSetting('log_changes', '1'),
            'log_sales' => $this->getSetting('log_sales', '1'),
        ];
    }

    public function saveSettings(array $settings): bool
    {
        if (isset($settings['log_changes'])) {
            $this->setSetting('log_changes', $settings['log_changes'] ? '1' : '0');
        }
        
        if (isset($settings['log_sales'])) {
            $this->setSetting('log_sales', $settings['log_sales'] ? '1' : '0');
        }
        
        return true;
    }

    /**
     * Handle item change event.
     */
    public function onItemChange(int $itemId): void
    {
        if (!$this->isEnabled() || $this->getSetting('log_changes', '1') !== '1') {
            return;
        }
        
        $this->log('info', "Item changed: ID {$itemId}");
    }

    /**
     * Handle item sale event.
     */
    public function onItemSale(array $saleData): void
    {
        if (!$this->isEnabled() || $this->getSetting('log_sales', '1') !== '1') {
            return;
        }
        
        $saleId = $saleData['sale_id_num'] ?? 'unknown';
        $this->log('info', "Item sale: ID {$saleId}");
    }
}