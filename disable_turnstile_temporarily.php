<?php
/**
 * Temporary script to disable Turnstile if you can't access the admin panel
 */

echo "🔧 Temporarily disabling Turnstile...\n\n";

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
    
    // Disable Turnstile  
    $stmt = $pdo->prepare("UPDATE {$prefix}app_config SET `value` = '0' WHERE `key` = 'turnstile_enable'");
    $stmt->execute();
    
    echo "✅ Turnstile has been temporarily disabled.\n";
    echo "🌐 You can now access your domain without Turnstile verification.\n";
    echo "🔧 To re-enable with proper keys, go to Admin → Configuration → General → Security\n\n";
    
    echo "📝 Next steps:\n";
    echo "1. Login to your admin panel\n";
    echo "2. Go to Configuration → General\n";
    echo "3. Either disable Turnstile or configure real keys from Cloudflare\n";
    echo "4. Get real keys from: https://developers.cloudflare.com/turnstile/get-started/\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
