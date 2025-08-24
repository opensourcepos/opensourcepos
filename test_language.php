<?php
/**
 * Test script to verify language files work correctly
 */

echo "=== Language File Test ===\n";

// Test Config language file
try {
    $config_lang = include 'app/Language/en/Config.php';
    
    $turnstile_keys = [
        'turnstile_enable',
        'turnstile_secret_key',
        'turnstile_secret_key_required',
        'turnstile_site_key',
        'turnstile_site_key_required',
        'turnstile_tooltip'
    ];
    
    echo "Checking Config.php language keys:\n";
    foreach ($turnstile_keys as $key) {
        if (isset($config_lang[$key])) {
            echo "✓ $key: '{$config_lang[$key]}'\n";
        } else {
            echo "✗ $key: MISSING\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Error loading Config.php: " . $e->getMessage() . "\n";
}

// Test Login language file
try {
    $login_lang = include 'app/Language/en/Login.php';
    
    echo "\nChecking Login.php language keys:\n";
    if (isset($login_lang['invalid_turnstile'])) {
        echo "✓ invalid_turnstile: '{$login_lang['invalid_turnstile']}'\n";
    } else {
        echo "✗ invalid_turnstile: MISSING\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error loading Login.php: " . $e->getMessage() . "\n";
}

echo "\n=== Language Test Complete ===\n";
?>
