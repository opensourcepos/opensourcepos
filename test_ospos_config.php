<?php

// Test how OSPOS config is loaded in validation context
require_once 'vendor/autoload.php';
require_once 'app/Config/Paths.php';

$paths = new Config\Paths();

// Define path constants
define('APPPATH', $paths->appDirectory);
define('SYSTEMPATH', $paths->systemDirectory);
define('ROOTPATH', $paths->rootDirectory);
define('FCPATH', $paths->fcDirectory);
define('WRITEPATH', $paths->writableDirectory);
define('TESTPATH', $paths->testsDirectory);

require_once SYSTEMPATH . 'bootstrap.php';

$app = new CodeIgniter\CodeIgniter($paths);
$app->initialize();

echo "Testing OSPOS config loading...\n\n";

try {
    // Test how the config is loaded in validation context
    $ospos_config = config(\Config\OSPOS::class);
    echo "OSPOS config loaded successfully\n";
    
    $settings = $ospos_config->settings;
    echo "Settings array loaded, count: " . count($settings) . "\n\n";
    
    // Check specific Turnstile keys
    $turnstile_keys = ['turnstile_enable', 'turnstile_site_key', 'turnstile_secret_key'];
    
    foreach ($turnstile_keys as $key) {
        if (isset($settings[$key])) {
            echo "✓ {$key}: " . $settings[$key] . "\n";
        } else {
            echo "✗ {$key}: NOT FOUND\n";
        }
    }
    
    echo "\n";
    
    // Check reCAPTCHA keys for comparison
    $recaptcha_keys = ['gcaptcha_enable', 'gcaptcha_site_key', 'gcaptcha_secret_key'];
    
    foreach ($recaptcha_keys as $key) {
        if (isset($settings[$key])) {
            echo "✓ {$key}: " . $settings[$key] . "\n";
        } else {
            echo "✗ {$key}: NOT FOUND\n";
        }
    }
    
    echo "\nTesting cache status...\n";
    $cache = \Config\Services::cache();
    $cached_settings = $cache->get('settings');
    if ($cached_settings) {
        echo "Cache found, decoding...\n";
        $decoded = decode_array($cached_settings);
        echo "Cache contains " . count($decoded) . " settings\n";
        
        foreach ($turnstile_keys as $key) {
            if (isset($decoded[$key])) {
                echo "Cache ✓ {$key}: " . $decoded[$key] . "\n";
            } else {
                echo "Cache ✗ {$key}: NOT FOUND\n";
            }
        }
    } else {
        echo "No cache found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
