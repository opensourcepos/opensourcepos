# OSPOS Plugin System

## Overview

The OSPOS Plugin System allows third-party integrations to extend the application's functionality without modifying core code. Plugins can listen to events, add configuration settings, and integrate with external services.

## Architecture

### Plugin Interface

All plugins must implement `App\Libraries\Plugins\PluginInterface`:

```php
interface PluginInterface
{
    public function getPluginId(): string;      // Unique identifier
    public function getPluginName(): string;    // Display name
    public function getPluginDescription(): string;
    public function getVersion(): string;
    public function registerEvents(): void;     // Register event listeners
    public function install(): bool;            // First-time setup
    public function uninstall(): bool;          // Cleanup
    public function isEnabled(): bool;
    public function getConfigView(): ?string;   // Configuration view path
    public function getSettings(): array;
    public function saveSettings(array $settings): bool;
}
```

### Base Plugin Class

Extend `App\Libraries\Plugins\BasePlugin` for common functionality:

```php
class MyPlugin extends BasePlugin
{
    public function getPluginId(): string { return 'my_plugin'; }
    public function getPluginName(): string { return 'My Plugin'; }
    // ... implement other methods
}
```

### Plugin Manager

The `PluginManager` class handles:
- Plugin discovery from `app/Plugins/` directory
- Loading and registering enabled plugins
- Managing plugin settings

## Available Events

OSPOS fires these events that plugins can listen to:

| Event | Arguments | Description |
|-------|-----------|-------------|
| `item_sale` | `array $saleData` | Fired when a sale is completed |
| `item_return` | `array $returnData` | Fired when a return is processed |
| `item_change` | `int $itemId` | Fired when an item is created/updated/deleted |
| `item_inventory` | `array $inventoryData` | Fired on inventory changes |
| `items_csv_import` | `array $importData` | Fired after items CSV import |
| `customers_csv_import` | `array $importData` | Fired after customers CSV import |

## Creating a Plugin

### 1. Create the Plugin Class

```php
<?php
// app/Plugins/MyPlugin.php

namespace App\Plugins;

use App\Libraries\Plugins\BasePlugin;
use CodeIgniter\Events\Events;

class MyPlugin extends BasePlugin
{
    public function getPluginId(): string
    {
        return 'my_plugin';
    }

    public function getPluginName(): string
    {
        return 'My Integration Plugin';
    }

    public function getPluginDescription(): string
    {
        return 'Integrates OSPOS with external service';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function registerEvents(): void
    {
        // Listen to OSPOS events
        Events::on('item_sale', [$this, 'onItemSale']);
        Events::on('item_change', [$this, 'onItemChange']);
    }

    public function onItemSale(array $saleData): void
    {
        if (!$this->isEnabled()) {
            return;
        }
        
        // Your integration logic here
        $this->log('info', "Processing sale: {$saleData['sale_id_num']}");
    }

    public function onItemChange(int $itemId): void
    {
        if (!$this->isEnabled()) {
            return;
        }
        
        // Your logic here
    }

    public function install(): bool
    {
        // Set default settings
        $this->setSetting('api_key', '');
        $this->setSetting('enabled', '0');
        return true;
    }

    public function getConfigView(): ?string
    {
        return 'Plugins/my_plugin/config';
    }
}
```

### 2. Create Configuration View (Optional)

```php
<?php
// app/Views/Plugins/my_plugin/config.php

echo form_open('plugins/manage/saveconfig/my_plugin');
echo form_label('API Key', 'api_key');
echo form_input('api_key', $settings['api_key'] ?? '');
echo form_submit('submit', 'Save');
echo form_close();
```

## Plugin Settings

Store plugin-specific settings using:

```php
// Get setting
$value = $this->getSetting('setting_key', 'default_value');

// Set setting
$this->setSetting('setting_key', 'value');

// Get all plugin settings
$settings = $this->getSettings();

// Save multiple settings
$this->saveSettings(['key1' => 'value1', 'key2' => 'value2']);
```

Settings are prefixed with the plugin ID (e.g., `my_plugin_api_key`).

## Example Plugins

### Example Plugin (app/Plugins/ExamplePlugin.php)
A demonstration plugin that logs events to the debug log.

### Mailchimp Plugin (app/Plugins/MailchimpPlugin.php)
Integrates with Mailchimp to sync customer data.

## Database

Plugin settings are stored in the `ospos_plugin_config` table:

```sql
CREATE TABLE ospos_plugin_config (
    `key` varchar(100) NOT NULL PRIMARY KEY,
    `value` text NOT NULL,
    created_at timestamp DEFAULT current_timestamp(),
    updated_at timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp()
);
```

## Event Flow

1. Application triggers event: `Events::trigger('item_sale', $data)`
2. PluginManager loads enabled plugins
3. Each plugin registers its listeners via `registerEvents()`
4. Events::on() callbacks are invoked automatically

## Plugin Directory Structure

```
app/
├── Plugins/
│   ├── ExamplePlugin.php
│   └── MailchimpPlugin.php
├── Libraries/
│   └── Plugins/
│       ├── PluginInterface.php
│       ├── BasePlugin.php
│       └── PluginManager.php
├── Models/
│   └── PluginConfigModel.php
└── Views/
    └── Plugins/
        ├── manage.php
        ├── example/
        │   └── config.php
        └── mailchimp/
            └── config.php
```

## Testing

Enable plugin logging to debug:

```php
$this->log('debug', 'Debug message');
$this->log('info', 'Info message');
$this->log('error', 'Error message');
```

Check logs in `writable/logs/`.