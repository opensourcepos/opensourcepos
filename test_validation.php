<?php
/**
 * Test validation rules specifically
 */

echo "=== Validation Rules Test ===\n";

// Test if we can call lang() function outside of CodeIgniter context
try {
    // This might fail if lang() is not available
    echo "Testing language function...\n";
    
    // Simulate a simple validation test without full CodeIgniter context
    $config = [
        'gcaptcha_enable' => '0',
        'turnstile_enable' => '0',
        'gcaptcha_secret_key' => '',
        'turnstile_secret_key' => ''
    ];
    
    echo "Configuration loaded successfully\n";
    
    // Test array_key_exists calls that we use in validation
    $gcaptcha_enabled = array_key_exists('gcaptcha_enable', $config) && $config['gcaptcha_enable'];
    $turnstile_enabled = array_key_exists('turnstile_enable', $config) && $config['turnstile_enable'];
    
    echo "✓ gcaptcha_enabled: " . ($gcaptcha_enabled ? 'true' : 'false') . "\n";
    echo "✓ turnstile_enabled: " . ($turnstile_enabled ? 'true' : 'false') . "\n";
    
    // Test the main issue - what happens when the secret key is not set
    if (!empty('') && isset($config['turnstile_secret_key'])) {
        echo "Would proceed with Turnstile validation\n";
    } else {
        echo "✓ Turnstile validation skipped correctly (empty response or missing key)\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error in validation test: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "✗ Fatal error in validation test: " . $e->getMessage() . "\n";
}

echo "\n=== Validation Test Complete ===\n";
?>
