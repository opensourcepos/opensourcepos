<?php
/**
 * Turnstile Domain Key Validator and Fixer
 * This script helps diagnose and fix Turnstile domain key issues
 */

echo "🔍 OSPOS Turnstile Domain Key Diagnostic Tool\n";
echo "=============================================\n\n";

// Read database config from .env file
$env_content = file_get_contents('.env');
preg_match('/database\.default\.hostname = \'?([^\']+)\'?/', $env_content, $host_match);
preg_match('/database\.default\.database = \'?([^\']+)\'?/', $env_content, $db_match);
preg_match('/database\.default\.username = \'?([^\']+)\'?/', $env_content, $user_match);
preg_match('/database\.default\.password = \'?([^\']+)\'?/', $env_content, $pass_match);
preg_match('/database\.default\.DBPrefix = \'?([^\']+)\'?/', $env_content, $prefix_match);

$host = $host_match[1] ?? 'localhost';
$database = $db_match[1] ?? 'osposs';
$username = $user_match[1] ?? 'root';
$password = $pass_match[1] ?? 'root';
$prefix = $prefix_match[1] ?? 'ospos_';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get current configuration
    $stmt = $pdo->prepare("SELECT `key`, `value` FROM {$prefix}app_config WHERE `key` LIKE 'turnstile_%' ORDER BY `key`");
    $stmt->execute();
    $config = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $config[$row['key']] = $row['value'];
    }
    
    echo "📊 Current Turnstile Configuration:\n";
    echo "-----------------------------------\n";
    echo "✅ Enabled: " . ($config['turnstile_enable'] ?? '0') . "\n";
    echo "🔑 Site Key: " . (empty($config['turnstile_site_key']) ? '(empty)' : substr($config['turnstile_site_key'], 0, 20) . '...') . "\n";
    echo "🔐 Secret Key: " . (empty($config['turnstile_secret_key']) ? '(empty)' : substr($config['turnstile_secret_key'], 0, 10) . '...' . substr($config['turnstile_secret_key'], -5)) . "\n\n";
    
    // Check if using test keys
    $using_test_keys = false;
    if (isset($config['turnstile_site_key']) && strpos($config['turnstile_site_key'], '1x000000000000') === 0) {
        $using_test_keys = true;
    }
    
    if ($using_test_keys) {
        echo "⚠️  WARNING: You are using TEST KEYS!\n";
        echo "-----------------------------------\n";
        echo "❌ Test keys only work on localhost\n";
        echo "❌ Test keys will FAIL on your actual domain\n";
        echo "❌ This causes the Turnstile widget to not display properly\n";
        echo "❌ Users may bypass validation with double-clicks\n\n";
        
        echo "🔧 SOLUTIONS:\n";
        echo "-------------\n";
        echo "1. IMMEDIATE FIX: Disable Turnstile completely\n";
        echo "2. PROPER FIX: Get real keys from Cloudflare\n\n";
        
        echo "📋 To get real Turnstile keys:\n";
        echo "1. Go to: https://dash.cloudflare.com/\n";
        echo "2. Create account or login\n";
        echo "3. Go to 'Turnstile' in the sidebar\n";
        echo "4. Click 'Add site'\n";
        echo "5. Enter your domain name\n";
        echo "6. Copy the Site Key and Secret Key\n";
        echo "7. Update in OSPOS Admin → Configuration → General\n\n";
        
        $choice = readline("Would you like to disable Turnstile now? (y/N): ");
        if (strtolower($choice) === 'y' || strtolower($choice) === 'yes') {
            $stmt = $pdo->prepare("UPDATE {$prefix}app_config SET `value` = '0' WHERE `key` = 'turnstile_enable'");
            $stmt->execute();
            echo "✅ Turnstile has been disabled.\n";
            echo "🌐 You can now access your domain normally.\n";
        }
    } else {
        echo "✅ You appear to be using real Turnstile keys.\n";
        echo "🔍 If you're still having issues, verify:\n";
        echo "1. Keys are for the correct domain\n";
        echo "2. Domain is spelled correctly in Cloudflare\n";
        echo "3. No typos in the keys when copied\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "📝 Check your .env file database configuration\n";
}
?>
