<?php

namespace App\Libraries\Plugins;

interface PluginInterface
{
    public function getPluginId(): string;

    public function getPluginName(): string;

    public function getPluginDescription(): string;

    public function getVersion(): string;

    /**
     * Register event listeners for this plugin.
     * 
     * Use Events::on() to register callbacks for OSPOS events.
     * This method is called when the plugin is loaded and enabled.
     * 
     * Example:
     *   Events::on('item_sale', [$this, 'onItemSale']);
     *   Events::on('item_change', [$this, 'onItemChange']);
     */
    public function registerEvents(): void;

    /**
     * Install the plugin.
     * 
     * Called when the plugin is first enabled. Use this to create database tables,
     * set default configuration values, and run any setup required.
     */
    public function install(): bool;

    /**
     * Uninstall the plugin.
     * 
     * Called when the plugin is being removed. Use this to remove database tables,
     * clean up configuration, etc.
     */
    public function uninstall(): bool;

    public function isEnabled(): bool;

    /**
     * Get the path to the plugin's configuration view file.
     * Returns null if the plugin has no configuration UI.
     * 
     * Example: 'Plugins/mailchimp/config'
     */
    public function getConfigView(): ?string;

    public function getSettings(): array;

    public function saveSettings(array $settings): bool;
}