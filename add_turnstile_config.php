<?php
/**
 * Temporary script to add Turnstile configuration to database
 * This can be deleted after the migration is run properly
 */

try {
    // Database configuration from Database.php
    $hostname = 'localhost';
    $username = 'root';
    $password = 'root';
    $database = 'osposs';
    $prefix = 'ospos_';
    
    // Connect to MySQL database
    $dsn = "mysql:host=$hostname;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Check if turnstile keys already exist
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM {$prefix}app_config WHERE `key` = ?");
    $stmt->execute(['turnstile_enable']);
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        // Add Turnstile configuration keys
        $turnstile_configs = [
            ['key' => 'turnstile_enable', 'value' => '0'],
            ['key' => 'turnstile_site_key', 'value' => ''],
            ['key' => 'turnstile_secret_key', 'value' => '']
        ];
        
        $stmt = $pdo->prepare("INSERT IGNORE INTO {$prefix}app_config (`key`, `value`) VALUES (?, ?)");
        
        foreach ($turnstile_configs as $config) {
            $stmt->execute([$config['key'], $config['value']]);
        }
        
        echo "✓ Turnstile configuration keys added to database successfully.\n";
        echo "  - turnstile_enable: 0\n";
        echo "  - turnstile_site_key: (empty)\n";
        echo "  - turnstile_secret_key: (empty)\n";
    } else {
        echo "✓ Turnstile configuration keys already exist in database.\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
    echo "\nThis could be because:\n";
    echo "1. Database server is not running\n";
    echo "2. Database credentials are incorrect\n";
    echo "3. Database 'osposs' doesn't exist\n";
    echo "4. Database connection settings need to be updated\n";
    echo "\nPlease check your database configuration in app/Config/Database.php\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
