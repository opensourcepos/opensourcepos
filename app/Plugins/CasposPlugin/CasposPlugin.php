<?php

namespace App\Plugins\CasposPlugin;

use App\Libraries\Plugins\BasePlugin;
use CodeIgniter\Events\Events;

class CasposPlugin extends BasePlugin
{
    public function getPluginId(): string
    {
        return 'caspos';
    }

    public function getPluginName(): string
    {
        return 'CASPOS Integration';
    }

    public function getPluginDescription(): string
    {
        return 'Azerbaijan government cash register integration for fiscal receipts';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function registerEvents(): void
    {
        Events::on('item_sale', [$this, 'onItemSale']);
        
        Events::on('view:receipt_actions', [$this, 'injectReceiptButton']);
        Events::on('view:customer_tabs', [$this, 'injectCustomerTab']);
        
        log_message('debug', 'CASPOS plugin events registered');
    }

    public function install(): bool
    {
        log_message('info', 'Installing CASPOS plugin');
        
        $this->setSetting('api_url', '');
        $this->setSetting('api_key', '');
        $this->setSetting('merchant_id', '');
        $this->setSetting('show_receipt_button', '1');
        
        return true;
    }

    public function uninstall(): bool
    {
        log_message('info', 'Uninstalling CASPOS plugin');
        return true;
    }

    public function getConfigView(): ?string
    {
        return 'Plugins/CasposPlugin/Views/config';
    }

    public function getSettings(): array
    {
        return [
            'api_url' => $this->getSetting('api_url', ''),
            'api_key' => $this->getSetting('api_key', ''),
            'merchant_id' => $this->getSetting('merchant_id', ''),
            'show_receipt_button' => $this->getSetting('show_receipt_button', '1'),
        ];
    }

    public function saveSettings(array $settings): bool
    {
        if (isset($settings['api_url'])) {
            $this->setSetting('api_url', $settings['api_url']);
        }
        
        if (isset($settings['api_key'])) {
            $this->setSetting('api_key', $settings['api_key']);
        }
        
        if (isset($settings['merchant_id'])) {
            $this->setSetting('merchant_id', $settings['merchant_id']);
        }
        
        if (isset($settings['show_receipt_button'])) {
            $this->setSetting('show_receipt_button', $settings['show_receipt_button'] ? '1' : '0');
        }
        
        return true;
    }

    public function onItemSale(array $saleData): void
    {
        log_message('info', "CASPOS: Processing sale {$saleData['sale_id_num']}");
    }

    public function injectReceiptButton(array $data): string
    {
        if ($this->getSetting('show_receipt_button', '1') !== '1') {
            return '';
        }
        
        return view('Plugins/CasposPlugin/Views/receipt_button', $data);
    }

    public function injectCustomerTab(array $data): string
    {
        return view('Plugins/CasposPlugin/Views/customer_tab', $data);
    }
}