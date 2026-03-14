<?php

namespace App\Libraries\Plugins;

/**
 * Plugin Interface
 * 
 * All plugins must implement this interface to be discovered and loaded by the PluginManager.
 * This ensures a standard contract for plugin lifecycle and event registration.
 */
interface PluginInterface
{
    /**
     * Get the unique identifier for this plugin.
     * Should be lowercase with underscores, e.g., 'mailchimp_integration'
     */
    public function getPluginId(): string;

    /**
     * Get the human-readable name of the plugin.
     */
    public function getPluginName(): string;

    /**
     * Get the plugin description.
     */
    public function getPluginDescription(): string;

    /**
     * Get the plugin version.
     */
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
     * Called when the plugin is first enabled. Use this to:
     * - Create database tables
     * - Set default configuration values
     * - Run any setup required
     * 
     * @return bool True if installation succeeded
     */
    public function install(): bool;

    /**
     * Uninstall the plugin.
     * 
     * Called when the plugin is being removed. Use this to:
     * - Remove database tables
     * - Clean up configuration
     * 
     * @return bool True if uninstallation succeeded
     */
    public function uninstall(): bool;

    /**
     * Check if the plugin is enabled.
     */
    public function isEnabled(): bool;

    /**
     * Get the path to the plugin's configuration view file.
     * Returns null if the plugin has no configuration UI.
     * 
     * Example: 'Plugins/mailchimp/config'
     */
    public function getConfigView(): ?string;

    /**
     * Get plugin-specific settings for the configuration view.
     * 
     * @return array Settings array to pass to the view
     */
    public function getSettings(): array;

    /**
     * Save plugin settings from configuration form.
     * 
     * @param array $settings The settings to save
     * @return bool True if settings were saved successfully
     */
    public function saveSettings(array $settings): bool;
}