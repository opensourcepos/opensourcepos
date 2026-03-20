# OSPOS Plugin System

## Overview

The OSPOS Plugin System allows third-party integrations to extend the application's functionality without modifying core code. Plugins can listen to events, add configuration settings, and integrate with external services.

## Installation

### Self-Contained Plugin Packages

Plugins are self-contained packages that can be installed by simply dropping the plugin folder into `app/Plugins/`:

```
app/Plugins/
├── CasposPlugin/              # Plugin directory (self-contained)
│   ├── CasposPlugin.php       # Main plugin class (required - must match directory name)
│   ├── Models/                # Plugin-specific models
│   │   └── CasposData.php
│   ├── Controllers/           # Plugin-specific controllers
│   │   └── Dashboard.php
│   ├── Views/                 # Plugin-specific views
│   │   └── config.php
│   ├── Language/              # Plugin-specific translations (self-contained)
│   │   ├── en/
│   │   │   └── CasposPlugin.php
│   │   └── es-ES/
│   │       └── CasposPlugin.php
│   ├── Libraries/            # Plugin-specific libraries
│   ├── Helpers/              # Plugin-specific helpers
│   └── config/               # Configuration files
│
├── MailchimpPlugin.php        # Or single-file plugins (simple plugins)
└── ExamplePlugin.php
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

Both formats are supported, but directory plugins allow for self-contained packages with their own MVC components.

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

1. **Core views define hook points** using the `plugin_content()` helper
2. **Plugins register listeners** for these view hooks in `registerEvents()`
3. **Content is conditionally rendered** only if the plugin is enabled

### Step 1: Adding Hook Points in Core Views

In your core view files, use the `plugin_content()` helper to define injection points:

```php
// In app/Views/sales/receipt.php
<div class="receipt-actions">
    <!-- Existing buttons -->
    <?= plugin_content('receipt_actions', ['sale' => $sale]) ?>
</div>

// In app/Views/customers/form.php
<ul class="nav nav-tabs">
    <!-- Existing tabs -->
    <?= plugin_content('customer_tabs', ['customer' => $customer]) ?>
</ul>
```

### Step 2: Plugin Registers View Hook

In your plugin class, register a listener that returns HTML content:

```php
class CasposPlugin extends BasePlugin
{
    public function registerEvents(): void
    {
        Events::on('item_sale', [$this, 'onItemSale']);
        
        // View hooks - inject content into core views
        Events::on('view:receipt_actions', [$this, 'injectReceiptButton']);
        Events::on('view:customer_tabs', [$this, 'injectCustomerTab']);
    }
    
    public function injectReceiptButton(array $data): string
    {
        // Return HTML from plugin's own view file
        return view('Plugins/CasposPlugin/Views/receipt_button', $data);
    }
    
    public function injectCustomerTab(array $data): string
    {
        return view('Plugins/CasposPlugin/Views/customer_tab', $data);
    }
}
```
```

### Plugin View Files

The plugin's view files are self-contained within the plugin directory:

```php
// app/Plugins/CasposPlugin/Views/receipt_button.php
<a href="javascript:void(0);" class="btn btn-info btn-sm" onclick="printCasposReceipt(<?= $sale['sale_id'] ?>)">
    <span class="glyphicon glyphicon-print">&nbsp;</span>
    Print Fiscal Receipt
</a>

// app/Plugins/CasposPlugin/Views/customer_tab.php
<li>
    <a href="#caspos_panel" data-toggle="tab">
        <span class="glyphicon glyphicon-print">&nbsp;</span>
        Fiscal Records
    </a>
</li>
```

### Helper Functions

The `plugin_helper.php` provides two functions:

```php
// Render plugin content for a hook point
plugin_content(string $section, array $data = []): string

// Check if any plugin has registered for a hook (for conditional rendering)
plugin_content_exists(string $section): bool
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
        $this->log('info', "Processing sale: {$saleData['sale_id_num']}");
    }

    public function onItemChange(int $itemId): void
    {
        $this->log('info', "Item changed: {$itemId}");
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
└── CasposPlugin/                    # Plugin directory
    ├── CasposPlugin.php             # Main class - namespace: App\Plugins\CasposPlugin
    ├── Models/                      # Plugin models - namespace: App\Plugins\CasposPlugin\Models
    │   └── CasposData.php
    ├── Controllers/                 # Plugin controllers - namespace: App\Plugins\CasposPlugin\Controllers
    │   └── Dashboard.php
    ├── Views/                       # Plugin views
    │   ├── config.php
    │   └── dashboard.php
    ├── Language/                    # Plugin translations (self-contained)
    │   ├── en/
    │   │   └── CasposPlugin.php
    │   └── es-ES/
    │       └── CasposPlugin.php
    └── Libraries/                   # Plugin libraries - namespace: App\Plugins\CasposPlugin\Libraries
        └── ApiClient.php
```

**Main Plugin Class:**

```php
<?php
// app/Plugins/CasposPlugin/CasposPlugin.php

namespace App\Plugins\CasposPlugin;

use App\Libraries\Plugins\BasePlugin;
use App\Plugins\CasposPlugin\Models\CasposData;
use CodeIgniter\Events\Events;

class CasposPlugin extends BasePlugin
{
    private ?CasposData $dataModel = null;
    
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
        return 'Azerbaijan government cash register integration';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function registerEvents(): void
    {
        Events::on('item_sale', [$this, 'onItemSale']);
    }

    private function getDataModel(): CasposData
    {
        if ($this->dataModel === null) {
            $this->dataModel = new CasposData();
        }
        return $this->dataModel;
    }

    public function onItemSale(array $saleData): void
    {
        // Use internal model
        $this->getDataModel()->saveSaleRecord($saleData);
        
        // Use internal library
        $apiClient = new \App\Plugins\CasposPlugin\Libraries\ApiClient();
        $apiClient->sendToGovernment($saleData);
    }

    public function install(): bool
    {
        // Create plugin-specific database table
        $this->getDataModel()->createTable();
        
        // Set default settings
        $this->setSetting('api_url', '');
        $this->setSetting('api_key', '');
        return true;
    }

    public function uninstall(): bool
    {
        // Drop plugin table
        $this->getDataModel()->dropTable();
        return true;
    }

    public function getConfigView(): ?string
    {
        return 'Plugins/CasposPlugin/Views/config';
    }
}
```

**Plugin Model:**

```php
<?php
// app/Plugins/CasposPlugin/Models/CasposData.php

namespace App\Plugins\CasposPlugin\Models;

use CodeIgniter\Model;

class CasposData extends Model
{
    protected $table = 'caspos_records';
    protected $primaryKey = 'id';
    protected $allowedFields = ['sale_id', 'fiscal_number', 'api_response', 'created_at'];
    
    public function createTable(): void
    {
        $forge = \Config\Database::forge();
        
        $fields = [
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'sale_id' => ['type' => 'INT', 'constraint' => 11],
            'fiscal_number' => ['type' => 'VARCHAR', 'constraint' => 100],
            'api_response' => ['type' => 'TEXT'],
            'created_at' => ['type' => 'TIMESTAMP']
        ];
        
        $forge->addField($fields);
        $forge->addKey('id', true);
        $forge->createTable($this->table, true);
    }
    
    public function dropTable(): void
    {
        $forge = \Config\Database::forge();
        $forge->dropTable($this->table, true);
    }
}
```

**Plugin Controller:**

```php
<?php
// app/Plugins/CasposPlugin/Controllers/Dashboard.php

namespace App\Plugins\CasposPlugin\Controllers;

use App\Controllers\Secure_Controller;
use App\Plugins\CasposPlugin\Models\CasposData;

class Dashboard extends Secure_Controller
{
    private CasposData $dataModel;
    
    public function __construct()
    {
        parent::__construct('plugins');
        $this->dataModel = new CasposData();
    }
    
    public function getIndex(): void
    {
        $data = ['records' => $this->dataModel->orderBy('created_at', 'DESC')->findAll(50)];
        echo view('Plugins/CasposPlugin/Views/dashboard', $data);
    }
}
```

**Plugin View:**

```php
<?php
// app/Plugins/CasposPlugin/Views/config.php

echo form_open('plugins/caspos/save');
echo form_label('API URL', 'api_url');
echo form_input('api_url', $settings['api_url'] ?? '');
echo form_submit('submit', 'Save');
echo form_close();
```

## Internationalization (Language Files)

Plugins can include their own language files, making them completely self-contained. This allows plugins to provide translations without modifying core language files.

### Plugin Language Directory Structure

```
app/Plugins/
└── CasposPlugin/
    ├── CasposPlugin.php
    ├── Language/
    │   ├── en/
    │   │   └── CasposPlugin.php      # English translations
    │   ├── es-ES/
    │   │   └── CasposPlugin.php      # Spanish translations
    │   └── de-DE/
    │       └── CasposPlugin.php      # German translations
    └── Views/
        └── config.php
```

### Language File Format

Each language file returns an array of translation strings:

```php
<?php
// app/Plugins/CasposPlugin/Language/en/CasposPlugin.php

return [
    'caspos_plugin_name' => 'CASPOS Integration',
    'caspos_plugin_desc' => 'Azerbaijan government cash register integration',
    'caspos_print_receipt' => 'Print Fiscal Receipt',
    'caspos_fiscal_number' => 'Fiscal Number',
    'caspos_api_url' => 'API URL',
    'caspos_api_key' => 'API Key',
    'caspos_configuration' => 'CASPOS Configuration',
    'caspos_sync_success' => 'Sale synchronized successfully',
    'caspos_sync_failed' => 'Failed to synchronize sale',
];
```

### Loading Language Strings in Plugins

The `BasePlugin` class can provide a helper method to load plugin-specific language strings:

```php
<?php
namespace App\Plugins\CasposPlugin;

use App\Libraries\Plugins\BasePlugin;
use CodeIgniter\Events\Events;

class CasposPlugin extends BasePlugin
{
    public function getPluginName(): string
    {
        return $this->lang('caspos_plugin_name');
    }
    
    public function getPluginDescription(): string
    {
        return $this->lang('caspos_plugin_desc');
    }
    
    public function onItemSale(array $saleData): void
    {
        $result = $this->sendToApi($saleData);
        
        if ($result['success']) {
            log_message('info', $this->lang('caspos_sync_success'));
        } else {
            log_message('error', $this->lang('caspos_sync_failed') . ': ' . $result['error']);
        }
    }
    
    protected function lang(string $key, array $data = []): string
    {
        $language = \Config\Services::language();
        $language->addLanguagePath(APPPATH . 'Plugins/CasposPlugin/Language/');
        return $language->getLine($key, $data);
    }
}
```

### Using Language Strings in Plugin Views

```php
<?php
// app/Plugins/CasposPlugin/Views/config.php

$language = \Config\Services::language();
$language->addLanguagePath(APPPATH . 'Plugins/CasposPlugin/Language/');
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3><?= esc(lang('caspos_configuration')) ?></h3>
    </div>
    <div class="panel-body">
        <?= form_open('plugins/caspos/save') ?>
        
        <div class="form-group">
            <label for="apiUrl"><?= esc(lang('caspos_api_url')) ?></label>
            <?= form_input('api_url', $settings['api_url'] ?? '', 'class="form-control"') ?>
        </div>
        
        <div class="form-group">
            <label for="apiKey"><?= esc(lang('caspos_api_key')) ?></label>
            <?= form_input('api_key', $settings['api_key'] ?? '', 'class="form-control"') ?>
        </div>
        
        <?= form_submit('submit', lang('caspos_save'), 'class="btn btn-primary"') ?>
        <?= form_close() ?>
    </div>
</div>
```

### Using Language Strings in View Hooks

```php
<?php
// app/Plugins/CasposPlugin/Views/receipt_button.php

$language = \Config\Services::language();
$language->addLanguagePath(APPPATH . 'Plugins/CasposPlugin/Language/');
?>

<a href="javascript:void(0);" class="btn btn-info btn-sm" onclick="printCasposReceipt(<?= $sale['sale_id'] ?>)">
    <span class="glyphicon glyphicon-print">&nbsp;</span>
    <?= esc(lang('caspos_print_receipt')) ?>
</a>
```

### BasePlugin Language Helper

Add this method to `BasePlugin` to simplify language loading:

```php
<?php
// app/Libraries/Plugins/BasePlugin.php

abstract class BasePlugin implements PluginInterface
{
    protected function lang(string $key, array $data = []): string
    {
        $language = \Config\Services::language();
        $pluginLangPath = APPPATH . 'Plugins/' . $this->getPluginDir() . '/Language/';
        
        if (is_dir($pluginLangPath)) {
            $language->addLanguagePath($pluginLangPath);
        }
        
        return $language->getLine($key, $data);
    }
    
    abstract protected function getPluginDir(): string;
}
```

### Benefits of Self-Contained Language Files

1. **Plugin Independence**: No need to modify core language files
2. **Easy Distribution**: Plugin zip includes all translations
3. **Fallback Support**: Missing translations fall back to English
4. **User Contributions**: Users can add translations to `Language/{locale}/` in the plugin directory

### Language File Naming Convention

Language files should be named after the plugin class (e.g., `CasposPlugin.php`) to avoid conflicts with core language files and other plugins.

```
Language/{locale}/{PluginClass}.php
```

This ensures language strings are loaded from the correct plugin's Language directory.

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

Settings are prefixed with the plugin ID (e.g., `caspos_api_key`) and stored in `ospos_plugin_config` table.

## Namespace Reference

| File Location | Namespace |
|--------------|-----------|
| `app/Plugins/MyPlugin.php` | `App\Plugins\MyPlugin` |
| `app/Plugins/CasposPlugin/CasposPlugin.php` | `App\Plugins\CasposPlugin\CasposPlugin` |
| `app/Plugins/CasposPlugin/Models/CasposData.php` | `App\Plugins\CasposPlugin\Models\CasposData` |
| `app/Plugins/CasposPlugin/Controllers/Dashboard.php` | `App\Plugins\CasposPlugin\Controllers\Dashboard` |
| `app/Plugins/CasposPlugin/Libraries/ApiClient.php` | `App\Plugins\CasposPlugin\Libraries\ApiClient` |
| `app/Plugins/CasposPlugin/Language/en/CasposPlugin.php` | *(Language file - returns array, no namespace)* |

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

For custom tables, plugins can create them during `install()` and drop them during `uninstall()`.

## Event Flow

1. Application triggers event: `Events::trigger('item_sale', $data)`
2. PluginManager recursively scans `app/Plugins/` directory
3. Each enabled plugin registers its listeners via `registerEvents()`
4. Events::on() callbacks are invoked automatically

## Testing

Enable plugin logging to debug:

```php
$this->log('debug', 'Debug message');
$this->log('info', 'Info message');
$this->log('error', 'Error message');
```

Check logs in `writable/logs/`.

## Distributing Plugins

Plugin developers can package their plugins as zip files:

```
CasposPlugin-1.0.0.zip
└── CasposPlugin/
    ├── CasposPlugin.php
    ├── Models/
    ├── Controllers/
    ├── Views/
    ├── Language/
    │   ├── en/
    │   │   └── CasposPlugin.php
    │   └── es-ES/
    │       └── CasposPlugin.php
    └── README.md                 # Plugin documentation
```

Users extract the zip to `app/Plugins/` and the plugin is ready to use.