<?php
// Check current Turnstile configuration status

echo "🔍 Checking current Turnstile configuration on localhost...\n\n";

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
    
    // Check Turnstile configuration
    $stmt = $pdo->prepare("SELECT `key`, `value` FROM {$prefix}app_config WHERE `key` LIKE 'turnstile_%' OR `key` LIKE 'gcaptcha_%' ORDER BY `key`");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📊 Current CAPTCHA Configuration:\n";
    echo "================================\n\n";
    
    foreach ($results as $row) {
        $key = $row['key'];
        $value = $row['value'];
        
        if (strpos($key, 'secret') !== false && !empty($value)) {
            $value = substr($value, 0, 10) . '...' . substr($value, -5); // Hide secret keys
        }
        
        if (strpos($key, 'turnstile') === 0) {
            $emoji = $row['value'] === '1' ? '✅' : ($row['value'] === '0' ? '❌' : '🔑');
        } else {
            $emoji = $row['value'] === '1' ? '🟡' : ($row['value'] === '0' ? '⚪' : '🔑');
        }
        
        echo "{$emoji} {$key}: {$value}\n";
    }
    
    echo "\n📝 Legend:\n";
    echo "✅ Turnstile Enabled    🟡 reCAPTCHA Enabled\n";
    echo "❌ Turnstile Disabled   ⚪ reCAPTCHA Disabled\n";
    echo "🔑 API Keys (partial display for security)\n\n";
    
    // Check which system is active
    $turnstile_enabled = false;
    $recaptcha_enabled = false;
    
    foreach ($results as $row) {
        if ($row['key'] === 'turnstile_enable' && $row['value'] === '1') {
            $turnstile_enabled = true;
        }
        if ($row['key'] === 'gcaptcha_enable' && $row['value'] === '1') {
            $recaptcha_enabled = true;
        }
    }
    
    echo "🎯 Active CAPTCHA System:\n";
    if ($turnstile_enabled) {
        echo "   🔵 Cloudflare Turnstile is ACTIVE\n";
        if ($recaptcha_enabled) {
            echo "   ℹ️  reCAPTCHA is also enabled but Turnstile takes priority\n";
        }
    } elseif ($recaptcha_enabled) {
        echo "   🟡 Google reCAPTCHA is ACTIVE\n";
    } else {
        echo "   ⚪ No CAPTCHA system is enabled\n";
    }
    
    echo "\n🌐 For localhost testing:\n";
    echo "   📍 Your site is running on: http://localhost:8000\n";
    echo "   ✅ Test keys are configured for localhost\n";
    echo "   🧪 All validation attempts will pass during testing\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
