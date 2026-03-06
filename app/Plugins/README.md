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
│   │   └── Caspos_data.php
│   ├── Controllers/           # Plugin-specific controllers
│   │   └── Dashboard.php
│   ├── Views/                 # Plugin-specific views
│   │   └── config.php
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
        if (!$this->isEnabled()) {
            return;
        }
        
        $this->log('info', "Processing sale: {$saleData['sale_id_num']}");
    }

    public function onItemChange(int $itemId): void
    {
        if (!$this->isEnabled()) {
            return;
        }
        
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
    │   └── Caspos_data.php
    ├── Controllers/                 # Plugin controllers - namespace: App\Plugins\CasposPlugin\Controllers
    │   └── Dashboard.php
    ├── Views/                       # Plugin views
    │   ├── config.php
    │   └── dashboard.php
    └── Libraries/                   # Plugin libraries - namespace: App\Plugins\CasposPlugin\Libraries
        └── Api_client.php
```

**Main Plugin Class:**

```php
<?php
// app/Plugins/CasposPlugin/CasposPlugin.php

namespace App\Plugins\CasposPlugin;

use App\Libraries\Plugins\BasePlugin;
use App\Plugins\CasposPlugin\Models\Caspos_data;
use CodeIgniter\Events\Events;

class CasposPlugin extends BasePlugin
{
    private ?Caspos_data $dataModel = null;
    
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
        
        // Use internal model
        $this->getDataModel()->saveSaleRecord($saleData);
        
        // Use internal library
        $apiClient = new \App\Plugins\CasposPlugin\Libraries\Api_client();
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
// app/Plugins/CasposPlugin/Models/Caspos_data.php

namespace App\Plugins\CasposPlugin\Models;

use CodeIgniter\Model;

class Caspos_data extends Model
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
use App\Plugins\CasposPlugin\Models\Caspos_data;

class Dashboard extends Secure_Controller
{
    private Caspos_data $dataModel;
    
    public function __construct()
    {
        parent::__construct('plugins');
        $this->dataModel = new Caspos_data();
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
| `app/Plugins/CasposPlugin/Models/Caspos_data.php` | `App\Plugins\CasposPlugin\Models\Caspos_data` |
| `app/Plugins/CasposPlugin/Controllers/Dashboard.php` | `App\Plugins\CasposPlugin\Controllers\Dashboard` |
| `app/Plugins/CasposPlugin/Libraries/Api_client.php` | `App\Plugins\CasposPlugin\Libraries\Api_client` |

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

## Example Plugin Structure

### Simple Event-Listening Plugin

```
app/Plugins/
└── ExamplePlugin.php              # Just logging - no models/controllers/views needed
```

### Complex Self-Contained Plugin

```
app/Plugins/
└── CasposPlugin/
    ├── CasposPlugin.php          # Main class, event handling
    ├── Models/
    │   ├── Caspos_data.php       # Database model
    │   └── Caspos_transaction.php
    ├── Controllers/
    │   ├── Dashboard.php         # Admin dashboard
    │   └── Settings.php          # Settings page
    ├── Views/
    │   ├── config.php            # Configuration form
    │   ├── dashboard.php         # Dashboard view
    │   └── transaction_list.php
    ├── Libraries/
    │   └── Api_client.php        # Government API client
    ├── Helpers/
    │   └── caspos_helper.php     # Helper functions
    └── config/
        └── routes.php            # Custom routes (optional)
```

This structure allows users to install a plugin by simply:

```bash
# Download/extract
cp -r CasposPlugin/ /path/to/ospos/app/Plugins/

# Plugin auto-discovered and available in admin UI
```

## Distributing Plugins

Plugin developers can package their plugins as zip files:

```
CasposPlugin-1.0.0.zip
└── CasposPlugin/
    ├── CasposPlugin.php
    ├── Models/
    ├── Controllers/
    ├── Views/
    └── README.md                 # Plugin documentation
```

Users extract the zip to `app/Plugins/` and the plugin is ready to use.