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

### Core Plugin System Files

```
app/
├── Libraries/
│   └── Plugins/
│       ├── PluginInterface.php   # Contract all plugins must implement
│       ├── BasePlugin.php        # Abstract base class with common functionality
│       └── PluginManager.php     # Discovers and loads plugins
├── Models/
│   └── Plugin_config.php         # Model for plugin settings storage
├── Controllers/
│   └── Plugins/
│       └── Manage.php            # Admin controller for plugin management
└── Views/
    └── plugins/
        └── manage.php            # Plugin management admin view
```

### Plugin Files Organization

Plugins are organized with a clear separation of concerns:

```
app/
├── Plugins/                      # EVENT ORCHESTRATORS
│   ├── ExamplePlugin.php         # Simple plugin (event handlers only)
│   ├── MailchimpPlugin.php       # Integration plugin
│   └── CasposPlugin.php          # Complex plugin needing MVC
│
├── Models/Plugins/               # DATA MODELS (for plugins needing custom tables)
│   └── Caspos_data.php           # Model for CASPOS API data
│
├── Controllers/Plugins/          # ADMIN CONTROLLERS (for plugin config UI)
│   └── Caspos.php                # Controller for CASPOS admin interface
│
└── Views/Plugins/                # ADMIN VIEWS (for plugin configuration)
    ├── example/
    │   └── config.php
    ├── mailchimp/
    │   └── config.php
    └── caspos/
        └── config.php
```

## Plugin Architecture Patterns

### Simple Plugin (Event Handlers Only)

For plugins that only need to listen to events and don't require custom database tables or complex admin UI:

```php
// app/Plugins/ExamplePlugin.php
class ExamplePlugin extends BasePlugin
{
    public function registerEvents(): void
    {
        Events::on('item_sale', [$this, 'onItemSale']);
    }
    
    public function onItemSale(array $saleData): void
    {
        // Simple logic - just log or make API calls
        $this->log('info', "Sale processed: {$saleData['sale_id_num']}");
    }
}
```

### Complex Plugin (Full MVC)

For plugins that need database tables, complex admin UI, or business logic:

**1. Plugin Class (Event Orchestrator)** - Entry point that registers events and coordinates with MVC components:

```php
// app/Plugins/CasposPlugin.php
namespace App\Plugins;

use App\Libraries\Plugins\BasePlugin;
use App\Models\Plugins\Caspos_data;
use CodeIgniter\Events\Events;

class CasposPlugin extends BasePlugin
{
    private ?Caspos_data $dataModel = null;
    
    public function registerEvents(): void
    {
        Events::on('item_sale', [$this, 'onItemSale']);
        Events::on('item_change', [$this, 'onItemChange']);
    }
    
    private function getDataModel(): Caspos_data
    {
        if ($this->dataModel === null) {
            $this->dataModel = new Caspos_data();
        }
        return $this->dataModel;
    }
    
    public function onItemSale(array $saleData): void
    {
        if (!$this->isEnabled()) {
            return;
        }
        
        // Use the model for data persistence
        $this->getDataModel()->saveSaleRecord($saleData);
        
        // Call external API
        $this->sendToGovernmentApi($saleData);
    }
    
    private function sendToGovernmentApi(array $saleData): void
    {
        // Integration logic
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
}
```

**2. Model (Data Persistence)** - For plugins needing custom database tables:

```php
// app/Models/Plugins/Caspos_data.php
namespace App\Models\Plugins;

use CodeIgniter\Model;

class Caspos_data extends Model
{
    protected $table = 'caspos_records';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'sale_id',
        'fiscal_number',
        'api_response',
        'created_at'
    ];
    
    public function createTable(): void
    {
        $forge = \Config\Database::forge();
        
        $fields = [
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'sale_id' => [
                'type' => 'INT',
                'constraint' => 11
            ],
            'fiscal_number' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'api_response' => [
                'type' => 'TEXT'
            ],
            'created_at' => [
                'type' => 'TIMESTAMP'
            ]
        ];
        
        $forge->addField($fields);
        $forge->addKey('id', true);
        $forge->addKey('sale_id');
        $forge->createTable($this->table, true);
    }
    
    public function saveSaleRecord(array $saleData): bool
    {
        return $this->insert([
            'sale_id' => $saleData['sale_id_num'],
            'fiscal_number' => $saleData['fiscal_number'] ?? '',
            'api_response' => ''
        ]);
    }
}
```

**3. Controller (Admin UI)** - For plugin configuration pages:

```php
// app/Controllers/Plugins/Caspos.php
namespace App\Controllers\Plugins;

use App\Controllers\Secure_Controller;
use App\Models\Plugins\Caspos_data;

class Caspos extends Secure_Controller
{
    private Caspos_data $dataModel;
    
    public function __construct()
    {
        parent::__construct('plugins');
        $this->dataModel = new Caspos_data();
    }
    
    public function getIndex(): void
    {
        $data = [
            'records' => $this->dataModel->orderBy('created_at', 'DESC')->findAll(50)
        ];
        echo view('Plugins/caspos/dashboard', $data);
    }
    
    public function postTestConnection(): \CodeIgniter\HTTP\ResponseInterface
    {
        // Test API connection
        return $this->response->setJSON(['success' => true]);
    }
}
```

**4. Views (Admin Interface)** - Configuration and dashboard:

```php
// app/Views/Plugins/caspos/config.php
<?= form_open('plugins/caspos/save') ?>
    <div class="form-group">
        <?= form_label('API URL', 'api_url') ?>
        <?= form_input('api_url', $settings['api_url'] ?? '') ?>
    </div>
    <div class="form-group">
        <?= form_label('API Key', 'api_key') ?>
        <?= form_input('api_key', $settings['api_key'] ?? '') ?>
    </div>
    <?= form_submit('submit', 'Save Settings') ?>
<?= form_close() ?>
```

### Architecture Summary

| Component | Directory | Purpose |
|-----------|-----------|---------|
| Event Orchestrator | `app/Plugins/` | Implements `PluginInterface`, registers listeners, coordinates logic |
| Data Models | `app/Models/Plugins/` | Database models for plugin-specific tables |
| Admin Controllers | `app/Controllers/Plugins/` | Controllers for plugin configuration UI |
| Admin Views | `app/Views/Plugins/` | Views for plugin configuration |

The **plugin class** in `app/Plugins/` acts as the entry point - it listens to events and coordinates with its own MVC components as needed. This keeps the architecture modular and maintains separation of concerns.

## Testing

Enable plugin logging to debug:

```php
$this->log('debug', 'Debug message');
$this->log('info', 'Info message');
$this->log('error', 'Error message');
```

Check logs in `writable/logs/`.