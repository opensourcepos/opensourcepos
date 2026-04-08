# OSPOS Plugin System

## Overview

The OSPOS Plugin System allows third-party integrations to extend the application's functionality without modifying core code. Plugins can listen to events, add configuration settings, and integrate with external services.

## Installation

### Self-Contained Plugin Packages

Plugins are self-contained packages that can be installed by simply dropping the plugin folder into `app/Plugins/`:

```
app/Plugins/
├── MailchimpPlugin/              # Plugin directory (self-contained)
│   ├── MailchimpPlugin.php       # Main plugin class (required - must match directory name)
│   ├── Language/                # Plugin-specific translations (self-contained)
│   │   ├── en/
│   │   │   └── MailchimpPlugin.php
│   │   └── es-ES/
│   │       └── MailchimpPlugin.php
│   └── Views/                   # Plugin-specific views
│       └── config.php
```

### Installation Steps

1. **Download the plugin** - Copy the plugin folder/file to `app/Plugins/`
2. **Auto-discovery** - The plugin will be automatically discovered on next page load
3. **Enable** - Enable it from the admin interface (Plugins menu)
4. **Configure** - Configure plugin settings if needed

### Plugin Discovery

The PluginManager recursively scans `app/Plugins/` directory:

- **Single-file plugins**: `app/Plugins/MyPlugin.php` with namespace `App\Plugins\MyPlugin`
- **Directory plugins**: `app/Plugins/MyPlugin/MyPlugin.php` with namespace `App\Plugins\MyPlugin\MyPlugin`

Both formats are supported, but directory plugins allow for self-contained packages with their own components.

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
- Plugin discovery from `app/Plugins/` directory (recursive scan)
- Loading and registering enabled plugins
- Managing plugin settings

**Important:** The PluginManager only calls `registerEvents()` for enabled plugins. Disabled plugins never have their event callbacks registered with `Events::on()`. This means **you do not need to check `$this->isEnabled()` in your callback methods** - if the callback is registered, the plugin is enabled.

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

## View Hooks (Injecting Plugin Content into Views)

Plugins can inject UI elements into core views using the event-based view hook system. This allows plugins to add buttons, tabs, or other content without modifying core view files.

### How It Works

1. **Core views define hook points** using the `pluginContent()` helper
2. **Plugins register listeners** for these view hooks in `registerEvents()`
3. **Content is rendered** only when the plugin is enabled

### Step 1: Adding Hook Points in Core Views

In your core view files, use the `pluginContent()` helper to define injection points:

```php
// In app/Views/sales/receipt.php
<div class="receipt-actions">
    <!-- Existing buttons -->
    <?= pluginContent('receipt_actions', ['sale' => $sale]) ?>
</div>

// In app/Views/customers/form.php
<ul class="nav nav-tabs">
    <!-- Existing tabs -->
    <?= pluginContent('customer_tabs', ['customer' => $customer]) ?>
</ul>
```

### Step 2: Plugin Registers View Hook

In your plugin class, register a listener that returns HTML content:

```php
class MailchimpPlugin extends BasePlugin
{
    public function registerEvents(): void
    {
        Events::on('customer_saved', [$this, 'onCustomerSaved']);
        
        // View hooks - inject content into core views
        Events::on('view:customer_tabs', [$this, 'injectCustomerTab']);
    }
    
    public function injectCustomerTab(array $data): string
    {
        return view('Plugins/MailchimpPlugin/Views/customer_tab', $data);
    }
}
```

### Plugin View Files

The plugin's view files are self-contained within the plugin directory:

```php
// app/Plugins/MailchimpPlugin/Views/customer_tab.php
<li>
    <a href="#mailchimp_panel" data-toggle="tab">
        <span class="glyphicon glyphicon-envelope">&nbsp;</span>
        Mailchimp
    </a>
</li>
```

### Helper Functions

The `plugin_helper.php` provides two functions:

```php
// Render plugin content for a hook point
pluginContent(string $section, array $data = []): string

// Check if any plugin has registered for a hook (for conditional rendering)
pluginContentExists(string $section): bool
```

### Standard Hook Points

Core views should define these standard hook points:

| Hook Name | Location | Usage |
|-----------|----------|-------|
| `view:receipt_actions` | Receipt view action buttons | Add receipt-related buttons |
| `view:customer_tabs` | Customer form tabs | Add customer-related tabs |
| `view:item_form_buttons` | Item form action buttons | Add item-related buttons |
| `view:sales_complete` | Sale complete screen | Post-sale integration UI |
| `view:reports_menu` | Reports menu | Add custom report links |

### Benefits

- **Self-Contained**: Plugin UI stays in plugin directory
- **Conditional**: Only renders when plugin is enabled
- **Data Access**: Pass context (sale, customer, etc.) to plugin views
- **Multiple Plugins**: Multiple plugins can hook the same location
- **Clean Separation**: Core views remain unmodified

## Creating a Plugin

### Simple Plugin (Single File)

For plugins that only need to listen to events without complex UI or database tables:

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
        Events::on('item_sale', [$this, 'onItemSale']);
        Events::on('item_change', [$this, 'onItemChange']);
    }

    public function onItemSale(array $saleData): void
    {
        log_message('info', "Processing sale: {$saleData['sale_id_num']}");
    }

    public function onItemChange(int $itemId): void
    {
        log_message('info', "Item changed: {$itemId}");
    }

    public function install(): bool
    {
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

### Complex Plugin (Self-Contained Directory)

For plugins that need database tables, controllers, models, and views:

```
app/Plugins/
└── MailchimpPlugin/                    # Plugin directory
    ├── MailchimpPlugin.php             # Main class - namespace: App\Plugins\MailchimpPlugin\MailchimpPlugin
    ├── Models/                         # Plugin models
    │   └── MailchimpData.php
    ├── Controllers/                    # Plugin controllers
    │   └── Dashboard.php
    ├── Views/                          # Plugin views
    │   ├── config.php
    │   └── dashboard.php
    ├── Language/                       # Plugin translations (self-contained)
    │   ├── en/
    │   │   └── MailchimpPlugin.php
    │   └── es-ES/
    │       └── MailchimpPlugin.php
    └── Libraries/                      # Plugin libraries
        └── ApiClient.php
```

**Main Plugin Class:**

```php
<?php
// app/Plugins/MailchimpPlugin/MailchimpPlugin.php

namespace App\Plugins\MailchimpPlugin;

use App\Libraries\Plugins\BasePlugin;
use App\Plugins\MailchimpPlugin\Models\MailchimpData;
use CodeIgniter\Events\Events;

class MailchimpPlugin extends BasePlugin
{
    private ?MailchimpData $dataModel = null;
    
    public function getPluginId(): string
    {
        return 'mailchimp';
    }

    public function getPluginName(): string
    {
        return 'Mailchimp';
    }

    public function getPluginDescription(): string
    {
        return 'Integrate with Mailchimp to sync customers to mailing lists.';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function registerEvents(): void
    {
        Events::on('customer_saved', [$this, 'onCustomerSaved']);
        Events::on('customer_deleted', [$this, 'onCustomerDeleted']);
    }

    private function getDataModel(): MailchimpData
    {
        if ($this->dataModel === null) {
            $this->dataModel = new MailchimpData();
        }
        return $this->dataModel;
    }

    public function onCustomerSaved(array $customerData): void
    {
        if (!$this->shouldSyncOnSave()) {
            return;
        }
        
        $this->getDataModel()->syncCustomer($customerData);
    }

    public function install(): bool
    {
        $this->setSetting('api_key', '');
        $this->setSetting('list_id', '');
        $this->setSetting('sync_on_save', '1');
        return true;
    }

    public function uninstall(): bool
    {
        $this->getDataModel()->dropTable();
        return true;
    }

    public function getConfigView(): ?string
    {
        return 'Plugins/MailchimpPlugin/Views/config';
    }
    
    protected function lang(string $key, array $data = []): string
    {
        $language = \Config\Services::language();
        $language->addLanguagePath(APPPATH . 'Plugins/MailchimpPlugin/Language/');
        return $language->getLine($key, $data);
    }
    
    protected function getPluginDir(): string
    {
        return 'MailchimpPlugin';
    }
}
```

## Internationalization (Language Files)

Plugins can include their own language files, making them completely self-contained. This allows plugins to provide translations without modifying core language files.

### Plugin Language Directory Structure

```
app/Plugins/
└── MailchimpPlugin/
    ├── MailchimpPlugin.php
    ├── Language/
    │   ├── en/
    │   │   └── MailchimpPlugin.php      # English translations
    │   ├── es-ES/
    │   │   └── MailchimpPlugin.php      # Spanish translations
    │   └── de-DE/
    │       └── MailchimpPlugin.php      # German translations
    └── Views/
        └── config.php
```

### Language File Format

Each language file returns an array of translation strings:

```php
<?php
// app/Plugins/MailchimpPlugin/Language/en/MailchimpPlugin.php

return [
    'mailchimp'                     => 'Mailchimp',
    'mailchimp_description'         => 'Integrate with Mailchimp to sync customers to mailing lists.',
    'mailchimp_api_key'             => 'Mailchimp API Key',
    'mailchimp_configuration'       => 'Mailchimp Configuration',
    'mailchimp_key_successfully'    => 'API Key is valid.',
    'mailchimp_key_unsuccessfully'  => 'API Key is invalid.',
];
```

### Loading Language Strings in Plugins

The `BasePlugin` class can provide a helper method to load plugin-specific language strings:

```php
protected function lang(string $key, array $data = []): string
{
    $language = \Config\Services::language();
    $language->addLanguagePath(APPPATH . 'Plugins/' . $this->getPluginDir() . '/Language/');
    return $language->getLine($key, $data);
}

protected function getPluginDir(): string
{
    return 'MailchimpPlugin';
}
```

### Benefits of Self-Contained Language Files

1. **Plugin Independence**: No need to modify core language files
2. **Easy Distribution**: Plugin zip includes all translations
3. **Fallback Support**: Missing translations fall back to English
4. **User Contributions**: Users can add translations to `Language/{locale}/` in the plugin directory

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

Settings are prefixed with the plugin ID (e.g., `mailchimp_api_key`) and stored in `ospos_plugin_config` table.

## Namespace Reference

| File Location | Namespace |
|--------------|-----------|
| `app/Plugins/MyPlugin.php` | `App\Plugins\MyPlugin` |
| `app/Plugins/MailchimpPlugin/MailchimpPlugin.php` | `App\Plugins\MailchimpPlugin\MailchimpPlugin` |
| `app/Plugins/MailchimpPlugin/Models/MailchimpData.php` | `App\Plugins\MailchimpPlugin\Models\MailchimpData` |
| `app/Plugins/MailchimpPlugin/Controllers/Dashboard.php` | `App\Plugins\MailchimpPlugin\Controllers\Dashboard` |
| `app/Plugins/MailchimpPlugin/Libraries/ApiClient.php` | `App\Plugins\MailchimpPlugin\Libraries\ApiClient` |
| `app/Plugins/MailchimpPlugin/Language/en/MailchimpPlugin.php` | *(Language file - returns array, no namespace)* |

## Database

Plugin settings are stored in the `ospos_plugin_config` table:

```sql
CREATE TABLE IF NOT EXISTS `ospos_plugin_config` (
    `key` varchar(100) NOT NULL,
    `value` text NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

For custom tables, plugins can create them during `install()` and drop them during `uninstall()`.

## Event Flow

1. Application triggers event: `Events::trigger('item_sale', $data)`
2. PluginManager recursively scans `app/Plugins/` directory
3. Each enabled plugin registers its listeners via `registerEvents()`
4. Events::on() callbacks are invoked automatically

## Testing

Enable plugin logging to debug:

```php
log_message('debug', 'Debug message');
log_message('info', 'Info message');
log_message('error', 'Error message');
```

Check logs in `writable/logs/`.

## Distributing Plugins

Plugin developers can package their plugins as zip files:

```
MailchimpPlugin-1.0.0.zip
└── MailchimpPlugin/
    ├── MailchimpPlugin.php
    ├── Models/
    ├── Controllers/
    ├── Views/
    ├── Language/
    │   ├── en/
    │   │   └── MailchimpPlugin.php
    │   └── es-ES/
    │       └── MailchimpPlugin.php
    └── README.md                 # Plugin documentation
```

Users extract the zip to `app/Plugins/` and the plugin is ready to use.
