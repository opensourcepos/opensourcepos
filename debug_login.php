<?php
/**
 * Debug script to test login functionality step by step
 */

// Test 1: Check if we can access basic PHP functionality
echo "=== Login Debug Test ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Current Directory: " . getcwd() . "\n\n";

// Test 2: Check if we can load the configuration
try {
    echo "Testing configuration loading...\n";
    
    // Database connection test
    $hostname = 'localhost';
    $username = 'root';
    $password = 'root';
    $database = 'osposs';
    $prefix = 'ospos_';
    
    $dsn = "mysql:host=$hostname;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "✓ Database connection successful\n";
    
    // Test 3: Check if Turnstile config exists
    $stmt = $pdo->prepare("SELECT `key`, `value` FROM {$prefix}app_config WHERE `key` LIKE 'turnstile_%' OR `key` LIKE 'gcaptcha_%'");
    $stmt->execute();
    $configs = $stmt->fetchAll();
    
    echo "\nCAPTCHA Configuration in database:\n";
    foreach ($configs as $config) {
        echo "  {$config['key']}: '{$config['value']}'\n";
    }
    
    // Test 4: Check for missing config values that might cause issues
    $required_configs = [
        'turnstile_enable', 'turnstile_site_key', 'turnstile_secret_key',
        'gcaptcha_enable', 'gcaptcha_site_key', 'gcaptcha_secret_key'
    ];
    
    $existing_keys = array_column($configs, 'key');
    $missing_keys = array_diff($required_configs, $existing_keys);
    
    if (!empty($missing_keys)) {
        echo "\n⚠️  Missing configuration keys:\n";
        foreach ($missing_keys as $key) {
            echo "  - $key\n";
        }
    } else {
        echo "\n✓ All required CAPTCHA configuration keys exist\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
}

// Test 5: Check if specific files exist and are readable
echo "\nFile existence check:\n";
$files_to_check = [
    'app/Controllers/Login.php',
    'app/Config/Validation/OSPOSRules.php',
    'app/Views/login.php',
    'app/Language/en/Login.php',
    'app/Language/en/Config.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✓ $file exists\n";
    } else {
        echo "✗ $file missing\n";
    }
}

echo "\n=== Debug Complete ===\n";
?>
