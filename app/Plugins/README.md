# OSPOS Plugin System

## Overview

The OSPOS Plugin System allows third-party integrations to extend the application's functionality without modifying core code. Plugins can listen to events, add configuration settings, and integrate with external services.

## Installation

### Self-Contained Plugin Packages

Plugins are self-contained packages that can be installed by simply dropping the plugin folder into `app/Plugins/`:

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

## Uninstall

> **Warning:** Uninstall is destructive and cannot be reversed. All plugin data will be permanently deleted.

### Uninstall Steps

1. **Disable the plugin** - The plugin must be disabled before the Uninstall button appears
2. **Click Uninstall** - Click the Uninstall button in the Plugins admin interface
3. **Remove files** - Delete the plugin folder from `app/Plugins/` manually

### What Happens During Uninstall

The framework automatically removes all plugin settings from the `ospos_plugin_config` table when uninstall is triggered. Any custom database tables created by the plugin must be dropped inside the plugin's own `uninstall()` method — the goal is to leave the database in exactly the state it was before the plugin was installed.

Implement `uninstall()` in your plugin class to drop any tables your plugin created:

```php
public function uninstall(): bool
{
    $forge = \Config\Database::forge();
    $forge->dropTable('my_plugin_data', true);
    return true;
}
```

If your plugin created no custom tables, the default `BasePlugin::uninstall()` no-op is sufficient — the framework handles `ospos_plugin_config` cleanup automatically.

> **Note:** Deleting the plugin files from `app/Plugins/` is a separate manual step. The Uninstall button only cleans up database state — it does not remove files from disk.

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

`BasePlugin` provides these protected helpers:

| Method | Signature | Description |
|--------|-----------|-------------|
| `getSetting` | `(string $key, mixed $default = null): mixed` | Read one plugin setting |
| `setSetting` | `(string $key, mixed $value): bool` | Write one plugin setting |
| `log` | `(string $level, string $message): void` | Write to plugin-specific log file (`writable/logs/plugin-{id}-{date}.log`) |
| `logTo` | `(string $logName, string $level, string $message): void` | Write to a named plugin log file (`writable/logs/plugin-{id}-{logName}-{date}.log`) |
| `renderView` | `(string $viewName, array $data = []): string` | Render a view from the plugin's own `Views/` directory |

#### `renderView()`

Resolves views relative to the plugin's own namespace, so you pass only the bare view name — no path prefix needed:

```php
// Renders App\Plugins\MyPlugin\Views\customer_tab.php
echo $this->renderView('customer_tab', $data);
```

The method derives the namespace from `get_class($this)`, so it works automatically for any plugin that follows the standard directory layout. Use it inside view-hook callbacks (with `echo`) or anywhere a rendered HTML string is needed.

### Plugin Manager

The `PluginManager` class handles:
- Plugin discovery from `app/Plugins/` directory (recursive scan)
- Loading and registering enabled plugins
- Managing plugin settings

**Important:** The PluginManager only calls `registerEvents()` for enabled plugins. Disabled plugins never have their event callbacks registered with `Events::on()`. This means **you do not need to check `$this->isEnabled()` in your callback methods** - if the callback is registered, the plugin is enabled.

## Available Events

OSPOS fires these events that plugins can listen to:

| Event                 | Arguments                                                         | Fired when                                              |
|-----------------------|-------------------------------------------------------------------|---------------------------------------------------------|
| `user_logged_in`      | `int $personId, array $pluginData = []`                           | User successfully authenticated via the login form      |
| `customer_loaded`     | `int $customerId, array $pluginData = []`                         | Customer form view is rendered (`Customers::getView()`) |
| `customer_saved`      | `array $customerIds, array $pluginData = []`                      | Customer created/updated via form save or CSV import    |
| `customer_deleted`    | `int $personId, string $email, array $pluginData = []`            | Customer deleted                                        |
| `item_saved`          | `array $itemIds, array $pluginData = []`                          | Item created/updated via form save or CSV import        |
| `item_deleted`        | `array $itemIds, array $pluginData = []`                          | Item(s) deleted                                         |
| `sale_completed`      | `int $saleIdNum, int $saleType, array $pluginData = []`           | Sale finalized and receipt rendered (non-return sales only) |
| `return_completed`    | `int $returnSaleId, int $originalSaleId, array $pluginData = []`  | Return finalized — fired instead of `sale_completed` when mode is return. `$originalSaleId` is the sale being returned against; `0` if the return was not initiated via `return_entire_sale()` |
| `receiving_completed` | `int $receivingId, string $mode, array $pluginData = []`          | Receiving finalized and items added to inventory        |

> **Note:** `customer_saved` and `item_saved` always receive an array of IDs.

> **Note:** With `$receivingId` plugins can call `model(Receiving::class)->get_receiving_items($receivingId)` to get all line items, or `get_info($receivingId)` for header and supplier details. `$mode` is `'receive'`, `'return'`, or `'requisition'` — mode is NOT stored in the database, so this argument is the only way to distinguish modes at event time. Single-record saves wrap the one ID in an array; CSV imports pass all successfully saved IDs.

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
class ExamplePlugin extends BasePlugin
{
    public function registerEvents(): void
    {
        Events::on('customer_saved', [$this, 'onCustomerSaved']);
        
        // View hooks - inject content into core views
        Events::on('view:customer_tabs', [$this, 'injectCustomerTab']);
    }
    
    public function injectCustomerTab(array $data): void
    {
        echo $this->renderView('customer_tab', $data);
    }
}
```

### Plugin View Files

Plugin view files live in the plugin's `Views/` subdirectory. `renderView('customer_tab', $data)` resolves to `app/Plugins/ExamplePlugin/Views/customer_tab.php`:

```php
// app/Plugins/ExamplePlugin/Views/customer_tab.php
<li>
    <a href="#Example_panel" data-toggle="tab">
        <span class="glyphicon glyphicon-envelope">&nbsp;</span>
        Example
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

These hook points are currently defined in core views:

| Hook (event key)              | View file                               | Data passed                       | Usage                                               |
|-------------------------------|-----------------------------------------|-----------------------------------|-----------------------------------------------------|
| `view:customer_tab_nav`       | `app/Views/customers/form.php:28`       | `['customer' => $person_info]`    | Add `<li>` tab navigation entries to customer form  |
| `view:customer_tab_panels`    | `app/Views/customers/form.php:326`      | `['customer' => $person_info]`    | Add `<div>` tab panel content to customer form      |
| `view:sales_receipt_buttons`  | `app/Views/sales/receipt.php:51`        | `['saleId' => $sale_id_num]`      | Add buttons to the receipt view                     |
| `view:sales_invoice_buttons`  | `app/Views/sales/invoice.php:59`        | `['saleId' => $sale_id_num]`      | Add buttons to the invoice view                     |
| `view:sales_quote_buttons`    | `app/Views/sales/quote.php:55`          | `['saleId' => $sale_id_num]`      | Add buttons to the quote view                       |
| `view:sales_work_order_buttons` | `app/Views/sales/work_order.php:57`   | `['saleId' => $sale_id_num]`      | Add buttons to the work order view                  |
| `view:sales_register_buttons` | `app/Views/sales/register.php`          | *(none)*                          | Add UI controls to the register checkout area       |
| `view:item_form_plugin_fields` | `app/Views/items/form.php:61`          | `['item' => $item_info]`          | Add UI controls (e.g. checkboxes, inputs) to the item add/edit form — any number of plugins may hook this |

The four sales document hooks are deliberately symmetrical: a plugin that delivers a
sale document (print, email, messaging) can register the same callback for all four and
branch on the document type. Only `saleId` is passed, so resolve any further sale or
customer data inside the plugin.

### Benefits

- **Self-Contained**: Plugin UI stays in plugin directory
- **Conditional**: Only renders when plugin is enabled
- **Data Access**: Pass context (sale, customer, etc.) to plugin views
- **Multiple Plugins**: Multiple plugins can hook the same location
- **Clean Separation**: Core views remain unmodified

## Passing Data to Plugins

Plugins sometimes need data from the user's browser — a toggle, a dropdown selection, a text input — that was injected into a core view via a view hook. Since that UI element lives outside the core form and outside the controller, getting that data to the event listener requires a deliberate mechanism. OSPOS provides two patterns depending on the use case.

### AJAX Calls to Plugin Controllers

For interactions that happen independently of a form submission (e.g. a button click, a real-time toggle that should persist), the plugin injects a view partial that makes its own AJAX call directly to the plugin controller:

```php
// In plugin view partial (e.g. Views/my_button.php)
<div class="btn btn-info btn-sm" id="my_plugin_action_button">Do Thing</div>
<script>
$('#my_plugin_action_button').click(function() {
    $.ajax({
        type: 'POST',
        url: '<?= site_url("plugins/myplugin/doThing") ?>',
        dataType: 'json'
    });
});
</script>
```

The plugin controller handles it:

```php
// In Controllers/MyPluginController.php
public function postDoThing(): ResponseInterface
{
    // ... plugin logic
    return $this->response->setJSON(['success' => true]);
}
```

Define the route in `Config/Routes.php`:

```php
$routes->post('plugins/myplugin/doThing', '\App\Plugins\MyPlugin\Controllers\MyPluginController::postDoThing');
```

Use this pattern when the action is self-contained and does not need to modify the outcome of the core form submission.

### Plugin Data via Form Submission (`$pluginData`)

When a plugin injects a UI control (checkbox, input, etc.) that should influence what the plugin does *at the moment a core form is submitted*, use the `$pluginData` mechanism. This avoids modifying core controller code and keeps the contract between core and plugins clean.

#### How It Works

1. **Core view** marks its form with `data-plugin-form`:

```php
<?= form_open("sales/complete", ['id' => 'buttons_form', 'data-plugin-form' => 'true']) ?>
```

2. **Core JS** (`plugin_data_helper.js`, bundled globally) listens for that form's submit event. It sweeps the entire page for any input with `data-plugin-field`, serializes their values to JSON, and injects a hidden `plugin_data` field into the form before it posts.

3. **Core controller** reads `plugin_data` from POST and passes it as the final argument to `Events::trigger()`:

```php
$pluginData = json_decode($this->request->getPost('plugin_data') ?? '{}', true) ?: [];
Events::trigger('sale_completed', $data['sale_id_num'], $sale_type, $pluginData);
```

4. **Plugin listener** receives `$pluginData` and reads its own namespaced key:

```php
public function onSaleCompleted(int $saleId, int $saleType, array $pluginData = []): void
{
    $sendToApi = $pluginData['myplugin_send_to_api'] ?? true;
    if (!$sendToApi) {
        return;
    }
    // ... proceed
}
```

#### Naming Convention — Namespaced Keys

All `data-plugin-field` values **must** be namespaced using the plugin ID as a prefix:

```
{pluginId}_{variableName}
```

Examples: `caspos_send_to_caspos`, `mailchimp_sync_customer`, `mystore_include_tax`.

This prevents key collisions when multiple plugins inject fields into the same view. The core knows nothing about the key names — it passes the entire object through unchanged.

#### Plugin View Partial

The plugin's injected partial only needs the `data-plugin-field` attribute — no JavaScript required:

```php
// Views/my_control.php
<div class="col-xs-6">
    <label for="myplugin_send_to_api" class="control-label checkbox">
        <input type="checkbox"
               name="myplugin_send_to_api"
               id="myplugin_send_to_api"
               data-plugin-field="myplugin_send_to_api"
               value="1"
               checked>
        <?= lang('MyPlugin.send_to_api') ?>
    </label>
</div>
```

The `plugin_data_helper.js` finds the element by `data-plugin-field` regardless of where it sits in the DOM — inside or outside the form, in any injected partial.

#### Thin Contract

The `$pluginData` array is intentionally opaque to the core. The core passes it through without inspecting or validating its contents. This means:

- **Only pass what the listener needs at decision time** — not full objects, not IDs the plugin can look up itself from `$saleId`.
- **Booleans and simple scalars only** — flags, selections, short strings. Avoid large payloads.
- **Default to the safe/enabled behavior** — use `$pluginData['key'] ?? true` so that if the field is absent (e.g. the view hook is not registered, or the plugin is toggled off), the plugin behaves as if enabled rather than silently skipping.

#### Which Events Support `$pluginData`

All events pass `$pluginData`. For events fired from a form marked `data-plugin-form`, the array is populated from the POST body. For events fired from AJAX delete calls or GET requests, it is always `[]`.

| Event                 | Form                       | View                                          | POST populated |
|-----------------------|----------------------------|-----------------------------------------------|----------------|
| `sale_completed`      | `#buttons_form`            | `app/Views/sales/register.php`                | Yes            |
| `receiving_completed` | `#finish_receiving_form`   | `app/Views/receivings/receiving.php`          | Yes            |
| `item_saved`          | `#item_form` / `#csv_form` | `app/Views/items/form.php` / `form_csv_import.php` | Yes       |
| `customer_saved`      | `#customer_form` / `#csv_form` | `app/Views/customers/form.php` / `form_csv_import.php` | Yes  |
| `user_logged_in`      | *(POST login form, no plugin fields)* | —                               | No (always `[]`) |
| `customer_loaded`     | *(GET request)*            | —                                             | No (always `[]`) |
| `item_deleted`        | *(AJAX, no form)*          | —                                             | No (always `[]`) |
| `customer_deleted`    | *(AJAX, no form)*          | —                                             | No (always `[]`) |

## Creating a Plugin

### Simple Plugin (Single File)

For plugins that only need to listen to events without complex UI or database tables:

```text
app/Plugins/
└── ExamplePlugin/                # Plugin directory (self-contained)
    ├── Config/                   # Plugin-specific routing (optional)
    │   └── Routes.php
    ├── Language/                 # Plugin-specific translations (self-contained)
    │   ├── en/
    │   │   └── ExamplePlugin.php
    ├── Views/                    # Plugin-specific views
    │   └── config.php
    ├── ExamplePlugin.php         # Main class - namespace: App\Plugins\ExamplePlugin\ExamplePlugin
    └── LICENSE                   # Plugin license (required)
```

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
        Events::on('sale_completed', [$this, 'onSaleComplete']);
        Events::on('item_saved', [$this, 'onItemSaved']);
        Events::on('receiving_complete', [$this, 'onReceivingComplete']);
    }

    public function onSaleComplete(int $saleIdNum, int $saleType, array $pluginData = []): void
    {
        log_message('info', "Sale completed: #{$saleIdNum} ({$saleType})");
    }

    public function onItemSaved(array $itemIds): void
    {
        log_message('info', 'Items saved: ' . implode(', ', $itemIds));
    }

    public function onReceivingComplete(int $receivingId, string $mode): void
    {
        log_message('info', "Receiving completed: #{$receivingId} ({$mode})");
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

```text
app/Plugins/
└── ExamplePlugin/                # Plugin directory
    ├── Config/                   # Plugin-specific routing
    │   └── Routes.php
    ├── Controllers/              # Plugin controllers
    │   └── ExampleController.php
    ├── Language/                 # Plugin translations (self-contained)
    │   ├── en/                   # IETF BCP 47 locale format per CodeIgniter standards
    │   │   └── ExamplePlugin.php
    │   └── es-ES/
    │       └── ExamplePlugin.php
    │── Libraries/                # Plugin libraries
    │   └── ApiClient.php
    ├── Migrations/               # Plugin database migrations (optional)
    │   └── 20260627120000_CreateExampleTable.php
    ├── Models/                   # Plugin models
    │   └── ExampleModel.php
    ├── Traits/                   # Shared PHP traits for plugin classes
    │   └── ExampleTrait.php
    ├── Tests/                    # Plugin unit tests (optional)
    │   └── ExamplePluginTest.php
    ├── Views/                    # Plugin views
    │   ├── config.php
    │   └── dashboard.php
    ├── ExamplePlugin.php         # Main class - namespace: App\Plugins\ExamplePlugin
    └── LICENSE                   # Plugin license (required)
```
**Main Plugin Class:**

```php
<?php
// app/Plugins/ExamplePlugin/ExamplePlugin.php

namespace App\Plugins\ExamplePlugin;

use App\Libraries\Plugins\BasePlugin;
use App\Plugins\ExamplePlugin\Models\ExampleData;
use CodeIgniter\Events\Events;

class ExamplePlugin extends BasePlugin
{
    private ?ExampleData $dataModel = null;
    
    public function getPluginId(): string
    {
        return 'Example';
    }

    public function getPluginName(): string
    {
        return 'Example';
    }

    public function getPluginDescription(): string
    {
        return 'Integrate with Example to sync customers to mailing lists.';
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

    private function getDataModel(): ExampleData
    {
        if ($this->dataModel === null) {
            $this->dataModel = new ExampleData();
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
        return 'Plugins/ExamplePlugin/Views/config';
    }
    
    protected function getPluginDir(): string
    {
        return 'ExamplePlugin';
    }
}
```

## Plugin Routes

Plugins can define their own routes in a `Config/Routes.php` file. Routes are *NOT* auto-loaded by the framework when the plugin directory is discovered.

### Defining Plugin Routes

Create `app/Plugins/ExamplePlugin/Config/Routes.php`:

```php
<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->post('plugins/example/action', '\App\Plugins\ExamplePlugin\Controllers\ExampleController::postAction');
$routes->get('plugins/example/dashboard', '\App\Plugins\ExamplePlugin\Controllers\ExampleController::getDashboard');
```

### Route Naming Convention

Use a consistent naming scheme for plugin routes:
- Prefix routes with `plugins/` followed by plugin identifier
- Examples: `plugins/mailchimp/checkApiKey`, `plugins/example/sync`

### Full Qualified Class Names

Always use fully qualified controller names:
- `\App\Plugins\ExamplePlugin\Controllers\ExampleController::methodName`

This ensures routes work correctly regardless of autoloader state.

### Inbound Webhook Routes

A plugin that integrates with an external provider may need to receive server-to-server
callbacks. Such a request carries no CSRF token and no session, so the route must be
both public and CSRF-exempt.

Name the route exactly `plugins/<plugin_id>/webhook`. `app/Config/Filters.php` exempts
that pattern (`plugins/*/webhook`) from the global CSRF filter, so no core change is
needed per plugin:

```php
// app/Plugins/ExamplePlugin/Config/Routes.php
$routes->get('plugins/example/webhook', '\App\Plugins\ExamplePlugin\Controllers\WebhookController::index');
$routes->post('plugins/example/webhook', '\App\Plugins\ExamplePlugin\Controllers\WebhookController::index');
```

The exemption is anchored, so only that exact path is exempt — sibling routes such as
`plugins/example/sync` keep full CSRF protection.

**The plugin is responsible for authenticating these requests.** Because the endpoint is
unauthenticated by design, the webhook controller must extend `BaseController` (not
`Secure_Controller`) and verify the caller itself — typically by recomputing the
provider's signature header over the raw request body with a shared secret and comparing
using `hash_equals()`. Never trust a webhook payload that failed that check.

## Internationalization (Language Files)

Plugins can include their own language files, making them completely self-contained. This allows plugins to provide translations without modifying core language files.

### Plugin Language Directory Structure

```text
app/Plugins/
└── ExamplePlugin/
    └── Language/
        ├── en/
        │   └── ExamplePlugin.php      # English translations
        ├── es-ES/
        │   └── ExamplePlugin.php      # Spanish translations
        └── de-DE/
            └── ExamplePlugin.php      # German translations
```

### Language File Format

Each language file returns an array of translation strings per CodeIgniter standards:

```php
<?php
// app/Plugins/ExamplePlugin/Language/en/ExamplePlugin.php

return [
    'Example'                     => 'Example',
    'Example_description'         => 'Integrate with Example to sync customers to mailing lists.',
    'Example_api_key'             => 'Example API Key',
    'Example_configuration'       => 'Example Configuration',
    'Example_key_successfully'    => 'API Key is valid.',
    'Example_key_unsuccessfully'  => 'API Key is invalid.',
];
```

### Loading Language Strings in Plugins

CodeIgniter automatically loads language strings from `app/Plugins/{PluginDir}/Language/{locale}/` for plugins.

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

Settings are prefixed with the plugin ID (e.g., `example_api_key`) and stored in `ospos_plugin_config` table.

## Namespace Reference

| File Location                                                 | Namespace                                                 |
|---------------------------------------------------------------|-----------------------------------------------------------|
| `app/Plugins/ExamplePlugin.php`                               | `App\Plugins`                                             |
| `app/Plugins/ExamplePlugin/ExamplePlugin.php`                 | `App\Plugins\ExamplePlugin\ExamplePlugin`                 |
| `app/Plugins/ExamplePlugin/Config/Routes.php`                 | *(Route file - no namespace)*                             |
| `app/Plugins/ExamplePlugin/Models/ExampleModel.php`           | `App\Plugins\ExamplePlugin\Models\ExampleModel`           |
| `app/Plugins/ExamplePlugin/Controllers/ExampleController.php` | `App\Plugins\ExamplePlugin\Controllers\ExampleController` |
| `app/Plugins/ExamplePlugin/Libraries/ApiClient.php`           | `App\Plugins\ExamplePlugin\Libraries\ApiClient`           |
| `app/Plugins/ExamplePlugin/Traits/ExampleTrait.php`           | `App\Plugins\ExamplePlugin\Traits\ExampleTrait`           |
| `app/Plugins/ExamplePlugin/Migrations/20260627120000_CreateExampleTable.php` | `App\Plugins\ExamplePlugin\Migrations\CreateExampleTable` |
| `app/Plugins/ExamplePlugin/Language/en/ExamplePlugin.php`     | *(Language file - returns array, no namespace)*           |
| `app/Plugins/ExamplePlugin/Tests/ExamplePluginTest.php`       | `App\Plugins\ExamplePlugin\Tests`                         |

## Database

Plugin settings are stored in the `ospos_plugin_config` table:

```sql
CREATE TABLE IF NOT EXISTS `ospos_plugin_config` (
    `key` varchar(100) NOT NULL,
    `value` text NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
```

For custom tables, plugins can create them during `install()` and drop them during `uninstall()`.

## Plugin Migrations

Plugins can ship their own database migrations. The PluginManager runs all pending migrations automatically on each request (once per session, before event listeners are registered).

### How It Works

1. On startup, `PluginManager::runPendingMigrations()` scans each enabled plugin's `Migrations/` directory
2. Files are matched by the pattern `YYYYMMDDhhmmss_ClassName.php` (14-digit timestamp prefix)
3. The last-executed timestamp is read from `ospos_plugin_migrations` for that plugin
4. Only files with a timestamp greater than the stored version are executed, in ascending order
5. After each successful `up()` call, the version is updated; on failure, remaining migrations are skipped

### Directory Structure

```text
app/Plugins/ExamplePlugin/
└── Migrations/
    ├── 20260627120000_CreateExampleTable.php
    └── 20260701090000_AddExampleColumn.php
```

### File Naming Convention

```
YYYYMMDDhhmmss_ClassName.php
```

- **Timestamp**: 14 digits — `YYYYMMDD` + `hhmmss` (e.g., `20260627120000`)
- **ClassName**: PascalCase, becomes the class name and the last part of the FQCN
- Files not matching this pattern are ignored

### Migration Class

```php
<?php

namespace App\Plugins\ExamplePlugin\Migrations;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Forge;

class CreateExampleTable
{
    public function __construct(
        private BaseConnection $db,
        private Forge $forge
    ) {}

    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'item_id'    => ['type' => 'INT', 'unsigned' => true],
            'guid'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('example_items', true);
    }
}
```

> **Note:** `down()` is not called automatically. Roll back by writing a new migration that reverses the change.

### Version Tracking

Migration state is stored in `ospos_plugin_migrations`:

| Column | Type | Notes |
|--------|------|-------|
| `plugin_id` | varchar(100) PK | value returned by `getPluginId()` |
| `version` | bigint unsigned | timestamp of last successful migration (0 = none run) |
| `ran_at` | datetime | when the last migration ran |

Each plugin tracks its version independently. The table is created by the core migration `20260627000000_PluginMigrationsTableCreate`.

### Changing a Schema After the Plugin Has Shipped

Editing an existing migration file does nothing for an installation that already has that plugin installed. `runPendingMigrations()` only executes files whose timestamp is *greater than* the version already recorded in `ospos_plugin_migrations` for that plugin — a file that already ran is never re-run, no matter what you change inside it.

This means: once a migration file has been part of any real install (yours, a tester's, a released version — anything beyond your own machine before the plugin's first install), any schema change must ship as a **new** migration file with a later timestamp, never as an edit to the existing one:

```
Migrations/
├── 20260627120000_CreateExampleTable.php   ← already shipped, don't touch
└── 20260701090000_AddLastErrorColumn.php   ← new file for the new column
```

Editing an already-applied file only *appears* to work on a machine where the plugin has never been installed yet (a fresh `install()` run picks up the edited file's current contents, because it's the first time it runs at all). It silently does nothing on any machine where the plugin was installed before the edit — including your own, after an uninstall/reinstall cycle masks the problem by re-running `install()` from scratch. Don't rely on that as a workaround; ship a new migration file instead so existing installs pick up the change on their next request, without requiring an uninstall/reinstall.

## Unit Tests

Plugins can ship their own test suite inside a `Tests/` subdirectory. PHPUnit discovers them automatically — no separate `phpunit.xml` per plugin is needed or wanted.

### Directory Structure

```text
app/Plugins/ExamplePlugin/
└── Tests/
    └── ExamplePluginTest.php    # and any additional *Test.php files
```

### Namespace

```
App\Plugins\ExamplePlugin\Tests
```

This resolves via the existing `App\` → `app/` PSR-4 mapping in `composer.json` — no autoload changes needed.

### Base Class

Extend `Tests\Support\PluginTestCase` instead of `CIUnitTestCase` directly. `PluginTestCase` calls `PluginManager::registerAllNamespaces()` in `setUp()` so plugin class namespaces resolve without the `pre_system` hook that normally fires during a real request. Tests that exercise only pure PHP logic (no plugin class loading) may extend `CIUnitTestCase` directly.

### Example

```php
<?php

namespace App\Plugins\ExamplePlugin\Tests;

use App\Plugins\ExamplePlugin\ExamplePlugin;
use Tests\Support\PluginTestCase;

class ExamplePluginTest extends PluginTestCase
{
    public function testGetPluginId(): void
    {
        $plugin = new ExamplePlugin();
        $this->assertSame('Example', $plugin->getPluginId());
    }

    public function testGetVersion(): void
    {
        $plugin = new ExamplePlugin();
        $this->assertSame('1.0.0', $plugin->getVersion());
    }
}
```

### Running Plugin Tests

```bash
# All suites (core + plugins)
composer test

# Plugins only
vendor/bin/phpunit --testsuite Plugins

# Single plugin
vendor/bin/phpunit app/Plugins/ExamplePlugin/Tests/
```

> **Do not** place plugin tests under the root `tests/` directory — that breaks self-containment. Do not bundle a per-plugin `phpunit.xml` — the root `phpunit.xml.dist` already includes `./app/Plugins` in the `Plugins` testsuite.

## Event Flow

1. Application triggers event: `Events::trigger('sale_completed', $saleIdNum, $saleType, $pluginData)`
2. PluginManager recursively scans `app/Plugins/` directory
3. Each enabled plugin registers its listeners via `registerEvents()`
4. Events::on() callbacks are invoked automatically

If a plugin event trigger is not available, create an issue in the opensourcepos repository requesting the event to be
added as a feature. Specify the event, where it should be triggered, and what data should be passed to the callback.

If a plugin event trigger exists but the data you need is not passed to the callback, please open an issue in the
opensourcepos repository requesting the data to be added as a feature.

## Logging

Plugins have two logging paths:

**Standard CI4 log** — use `log_message()` directly; goes to `writable/logs/log-{date}.log` only:

```php
log_message('debug', 'Debug message');
```

**Plugin-specific log** — goes to a dedicated plugin file in `writable/logs/`, NOT the CI4 log:

```php
// From inside a plugin class (pluginId inferred automatically):
$this->log('debug', 'Debug message');
// → writable/logs/plugin-{id}-{date}.log

// Named log channel (useful for separating API calls, sync events, etc.):
$this->logTo('api', 'debug', 'API response received');
// → writable/logs/plugin-{id}-api-{date}.log

// From library classes inside a plugin (pass pluginId explicitly):
log_plugin_message('myplugin', 'debug', 'Sync started');
log_plugin_message('myplugin', 'debug', 'API call', 'api');
```

Check logs in `writable/logs/`.

### Gating Debug-Only Log Lines

`'debug'` is a log *level*, not an on/off switch — passing it as the level does not stop the line from being written. If a plugin has its own `debug_mode` setting (a toggle in its config view), every debug-only call (request/response dumps, timing, verbose traces) must be wrapped in an explicit check against that setting, so users who haven't enabled it don't get a log file full of noise:

```php
// Before — always logs, regardless of the plugin's debug_mode setting
log_plugin_message('debug', 'Request Headers: ' . json_encode($headers), 'myplugin', 'api');

// After — only logs when debug_mode is enabled
if ($this->debugMode) {
    log_plugin_message('debug', 'Request Headers: ' . json_encode($headers), 'myplugin', 'api');
}
```

Real errors are the opposite case: log them at `error` (or `warning`) to the plugin's **standard** log — `$this->log('error', ...)` or `log_plugin_message('error', ..., $pluginId)` with no channel argument — so they show up in `plugin-{id}-{date}.log` without the user needing to know to open a named channel file. Reserve `logTo()` / named channels (e.g. `'api'`) for high-volume or verbose diagnostic data, not for the error signal itself — an error logged only to a named channel is effectively invisible to anyone not already debugging that specific subsystem.

### Interpreting HTTP API Responses

When calling an external API, a response body that successfully parses as JSON is **not** evidence that the call succeeded. Error responses are frequently valid JSON too (e.g. `{"message": "Api Key not found!"}` on a 422). Always branch on the HTTP status code first, and only decode/use the body once the status confirms success:

```php
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode < 200 || $httpCode >= 300) {
    log_plugin_message('error', "Request failed: HTTP {$httpCode}: {$response}", 'myplugin');
    return false;
}

return json_decode($response, true) ?? true;
```

Checking `json_decode($response) !== null` alone will treat a well-formed error body as success — the failure never gets logged as an error, and callers upstream never learn the call failed.

## Toast Notifications (Pop-up Alerts)

OSPOS uses [Bootstrap Notify](https://github.com/mouse0270/bootstrap-notify) for in-page toast notifications via `$.notify()`. It is loaded globally on every page.

### From JavaScript (AJAX responses)

The standard pattern for plugin AJAX calls is to fire a toast in the response callback:

```javascript
$.ajax({
    type: 'POST',
    url: '<?= site_url("plugins/myplugin/doThing") ?>',
    dataType: 'json',
    success: function(response) {
        $.notify({ message: response.message }, { type: response.success ? 'success' : 'danger' });
    },
    error: function() {
        $.notify({ message: 'Something went wrong.' }, { type: 'danger' });
    }
});
```

Available `type` values: `success`, `danger`, `warning`, `info`.

### From PHP (after a page load or redirect)

Use this approach only when the form actually navigates to a new page afterward (e.g. `sale_completed` → the receipt view). If the form instead closes as a JS-driven modal and the underlying page is refreshed by AJAX (e.g. `item_saved`, `customer_saved`), flash data will not be read — see [From a Non-Navigating Form](#from-a-non-navigating-form-eg-item_saved-customer_saved) below instead.

When an event handler runs server-side and needs to surface an error or message on the next rendered page, use CI4 session flash data. Store the message in the event handler:

```php
public function onSaleCompleted(int $saleId, int $saleType, array $pluginData = []): void
{
    $response = $this->myLibrary->processTransaction($saleId);

    if (!$response->isSuccess()) {
        session()->setFlashdata('myplugin_error', $response->getMessage());
    }
}
```

Then read it in the injected view partial that is rendered on that same page:

```php
// In your plugin view partial (Views/my_partial.php)
<script>
$(document).ready(function() {
    <?php $error = session()->getFlashdata('myplugin_error'); ?>
    <?php if (!empty($error)): ?>
    $.notify({ message: <?= json_encode($error) ?> }, { type: 'danger' });
    <?php endif; ?>
});
</script>
```

Flash data is consumed on the first read and automatically cleared — no cleanup needed. Use `json_encode()` to safely embed the PHP string into JS without XSS risk.

### From a Non-Navigating Form (e.g. `item_saved`, `customer_saved`)

Some core forms submit via AJAX and never navigate to a new page — the item and customer edit forms are modals that close via JS, and the table behind them refreshes through `table_support.handle_submit()` (`public/js/manage_tables.js`), a pure client-side operation with no server-side page render. Flash data set in an `item_saved`/`customer_saved` listener sits unread until some unrelated future full page load, which is not useful feedback for the user who just clicked Submit.

Instead, follow the same self-contained pattern as [AJAX Calls to Plugin Controllers](#ajax-calls-to-plugin-controllers) above: store the outcome, expose it through a small endpoint, and have the injected view partial fetch and toast it right after the core form's own AJAX call completes.

1. **Store the outcome** on the plugin's own row, keyed by the relevant id, inside the event listener:

```php
public function onItemSaved(array $itemIds, array $pluginData = []): void
{
    $result = $this->myLibrary->sync($itemIds);

    $this->getDataModel()->setLastError(
        $itemIds[0],
        $result->isSuccess() ? null : $result->getMessage()
    );
}
```

2. **Expose a small endpoint** to read it back:

```php
// Controllers/MyPluginController.php
public function getLastError(int $itemId): ResponseInterface
{
    $error = $this->getDataModel()->getLastError($itemId);
    return $this->response->setJSON(['error' => $error]);
}
```

```php
// Config/Routes.php
$routes->get('plugins/myplugin/lastError/(:num)', '\App\Plugins\MyPlugin\Controllers\MyPluginController::getLastError/$1');
```

3. **Poll it from the injected view partial**, right after the core form's save call finishes, using `$(document).ajaxSuccess()` filtered to the core form's endpoint:

```javascript
// In your plugin view partial (e.g. Views/my_item_field.php)
$(document).ajaxSuccess(function(event, xhr, settings) {
    if (!settings.url || settings.url.indexOf('items/save') === -1) {
        return;
    }

    const itemId = $('#item_id').val();

    $.get('<?= site_url('plugins/myplugin/lastError') ?>/' + itemId, {}, function(response) {
        if (response.error) {
            $.notify({ message: response.error }, { type: 'danger' });
        }
    }, 'json');
});
```

This fires the toast the moment the modal closes, without any change to core JS or the core save endpoint's response shape.

## Registering Modules (Admin Menu Entries)

A **module** is a menu entry in the OSPOS admin sidebar. Registering one gives the plugin its own page in the navigation and wires up the permission system so access can be granted per user.

### Register in `install()`

Use `BasePlugin::registerModule()` inside your `install()` method:

```php
public function install(): bool
{
    // ... create tables, set defaults ...
    $this->registerModule('myplugin', 500, 'office');
    return true;
}
```

Parameters:

| Parameter | Type | Description |
|---|---|---|
| `$module_id` | string | **Must be the plugin ID or prefixed with `{plugin_id}_`** (e.g. `myplugin` or `myplugin_dashboard`) — enforced; other ids are rejected |
| `$sort` | int | Position in the menu list (lower = higher up). Default: 500 |
| `$admin_menu_group` | string | Menu group: `'office'`, `'reports'`, etc. |

`registerModule()` is idempotent — safe to call multiple times. It inserts the module and permission rows and auto-grants access to `person_id = 1` (the admin user).

### Automatic Cleanup on Uninstall

You do **not** need to unregister anything in `uninstall()`. When the plugin is uninstalled, the framework removes every module and permission whose id is the plugin id or starts with `{plugin_id}_` — the same way plugin settings in `ospos_plugin_config` are cleaned up. User grants cascade via foreign key. This is why the id naming convention is enforced: it is how the framework knows which rows belong to your plugin.

`unregisterModule()` and `unregisterSubPermission()` still exist for the rare case where you want to remove a module or permission while the plugin stays installed (e.g. a feature toggle or plugin upgrade).

### Language Keys

Views display module names and descriptions by calling:

```php
lang("Module.{$module->module_id}")       // display name
lang("Module.{$module->module_id}_desc")  // description
```

CI4 splits on the dot: `Module` is the **file name**, the rest is the **key**. This means the strings must live in a file named `Module.php` — they cannot be merged into your plugin's main language file (e.g. `MyPlugin.php`).

The file must be inside the plugin's own `Language/{locale}/` directory, not in `app/Language/`. PluginManager registers the plugin's PSR-4 namespace at startup; CI4's FileLocator searches all registered namespaces when loading language files, so it finds `app/Plugins/MyPlugin/Language/en/Module.php` automatically.

Create `Language/en/Module.php` in your plugin directory:

```php
<?php
// app/Plugins/MyPlugin/Language/en/Module.php
return [
    'myplugin'      => 'My Plugin',
    'myplugin_desc' => 'Manage My Plugin settings and operations.',
];
```

For other locales, create the same file under `Language/{locale}/` with an empty string for untranslated keys — CI4 falls back to `en` automatically:

```php
<?php
// app/Plugins/MyPlugin/Language/es-ES/Module.php
return [
    'myplugin'      => 'Mi Plugin',
    'myplugin_desc' => '',
];
```

### Controller and Route

The module menu link routes to `/{module_id}`. Create a controller and register the route in `Config/Routes.php`:

```php
$routes->get('myplugin', '\App\Plugins\MyPlugin\Controllers\MyPluginController::getIndex');
$routes->get('myplugin/(:any)', '\App\Plugins\MyPlugin\Controllers\MyPluginController::getIndex');
```

The controller should extend `Secure_Controller` so the permission check is enforced automatically:

```php
class MyPluginController extends \App\Controllers\Secure_Controller
{
    public function __construct()
    {
        parent::__construct('myplugin');
    }

    public function getIndex(): string
    {
        return view('App\Plugins\MyPlugin\Views\dashboard');
    }
}
```

Passing the `module_id` to `Secure_Controller`'s constructor restricts access to users who have been granted that permission.

### Module Icon

By default, OSPOS looks for a built-in SVG at `public/images/menubar/{module_id}.svg`. To supply a custom icon from your plugin, listen to the `view:module_icon_{module_id}` event and output an `<img>` tag pointing to your icon route.

**1. Register the event in `registerEvents()`:**

```php
Events::on('view:module_icon_myplugin', [$this, 'injectModuleIcon']);
```

**2. Add the handler:**

```php
public function injectModuleIcon(): void
{
    echo $this->renderView('module_icon');
}
```

**3. Create `Views/module_icon.php`:**

```php
<img src="<?= base_url('plugins/myplugin/icon') ?>" style="border: none;" alt="My Plugin">
```

> **Important:** Use only `style="border: none;"` — no inline `width` or `height`. OSPOS CSS controls sizing automatically:
> - Navbar: 24px wide (`.navbar .menu-icon img`)
> - Home/office grid: 64px tall (`#home_module_list img`, `#office_module_list img`)
>
> Adding inline size constraints will override these rules and break one or both locations.

**4. Serve the icon file via a route.** Add to `Config/Routes.php`:

```php
$routes->get('plugins/myplugin/icon', '\App\Plugins\MyPlugin\Controllers\MyPluginController::getIcon');
```

Add the controller method — note this route must **not** extend `Secure_Controller` (or use a public controller) so the browser can fetch the image without an auth redirect:

```php
public function getIcon(): \CodeIgniter\HTTP\ResponseInterface
{
    $path = APPPATH . 'Plugins/MyPlugin/my-icon.svg';
    return $this->response
        ->setHeader('Content-Type', 'image/svg+xml')
        ->setBody(file_get_contents($path));
}
```

Store the SVG file directly in your plugin directory (e.g. `app/Plugins/MyPlugin/my-icon.svg`). PNG is also supported — use `image/png` as the content type.

### Sub-permissions

For finer-grained access control within a module, register sub-permissions after the module:

```php
public function install(): bool
{
    $this->registerModule('myplugin', 500, 'office');
    $this->registerSubPermission('myplugin_reports', 'myplugin');
    return true;
}
```

Check sub-permissions in the controller or view:

```php
$employee = model(\App\Models\Employee::class);
if ($employee->has_grant('myplugin_reports', session('person_id'))) {
    // show reports section
}
```

## Distributing Plugins

Plugin developers can package their plugins as zip files:

```text
ExamplePlugin-1.0.0.zip
└── ExamplePlugin/
    ├── Controllers/
    ├── Language/
    │   ├── en/
    │   │   └── ExamplePlugin.php
    │   └── es-ES/
    │       └── ExamplePlugin.php
    ├── Libraries/
    │   └── ApiClient.php
    ├── Models/
    ├── Tests/
    ├── Traits/
    ├── Views/
    ├── ExamplePlugin.php
    └── LICENSE
```

Users extract the zip to `app/Plugins/` and the plugin is ready to use.

### License Requirement

Every plugin **must** include a `LICENSE` file in the root of the plugin directory. This file defines the legal terms under which the plugin can be used, modified, and redistributed.

The `LICENSE` file should clearly define:

- **Copyright**: Ownership and copyright notices
- **Usage Rights**: How the plugin can be used (personal, commercial, etc.)
- **Modification Rights**: Whether and how the plugin can be modified
- **Redistribution Rights**: Terms for redistributing the plugin (with or without modifications)
- **Warranty and Liability**: Any warranties or liability disclaimers
- **Support Terms**: Unless developed by opensourcepos, indication should be made that support is provided by the plugin developer, not opensourcepos

Common license types include:

- **MIT License**: Permissive license with minimal restrictions
- **GPL-3.0**: Copyleft license requiring derivative works to use the same license
- **Apache-2.0**: Permissive license with patent grants
- **Proprietary**: Custom commercial license with specific terms

Plugin developers are responsible for ensuring their license complies with any third-party dependencies included in the plugin.
