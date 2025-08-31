<?php
// Set Turnstile test keys for localhost testing

echo "Setting up Turnstile test keys for localhost testing...\n\n";

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

echo "Database config: $username@$host/$database (prefix: $prefix)\n\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Turnstile test keys (these work with any domain including localhost)
    $test_site_key = '1x00000000000000000000AA';      // Always passes
    $test_secret_key = '1x0000000000000000000000000000000AA'; // Always passes
    
    // Update or insert Turnstile configuration
    $updates = [
        'turnstile_site_key' => $test_site_key,
        'turnstile_secret_key' => $test_secret_key,
        'turnstile_enable' => '1'
    ];
    
    foreach ($updates as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO {$prefix}app_config (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
        $stmt->execute([$key, $value]);
        echo "✓ Set $key = $value\n";
    }
    
    echo "\n🎉 Turnstile test configuration completed!\n";
    echo "\nNow you can test Turnstile on localhost.\n";
    echo "The test keys will always validate successfully.\n\n";
    echo "📝 Next steps:\n";
    echo "1. Clear your browser cache\n";
    echo "2. Go to the login page\n";
    echo "3. You should see Turnstile widget\n";
    echo "4. For production, replace with real Cloudflare Turnstile keys\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
