<?php

/**
 * Standalone CLI worker used by ConcurrentDbRaceTrait::raceTwoProcesses().
 * Bootstraps CodeIgniter in its own process so the model method call below
 * runs on a genuinely separate DB connection from any sibling worker,
 * exercising real concurrent access instead of simulating it sequentially.
 *
 * Usage: php RaceWorker.php <method> <arg> [<arg> ...]
 */

chdir(dirname(__DIR__, 2));

require __DIR__ . '/../../vendor/codeigniter4/framework/system/Test/bootstrap.php';

$method = $argv[1];
$args   = array_slice($argv, 2);

$readyFile = getenv('RACE_READY_FILE');
$goFile    = getenv('RACE_GO_FILE');

file_put_contents($readyFile, '1');

while (!file_exists($goFile)) {
    usleep(1000);
}

$result = match ($method) {
    'giftcard' => model(App\Models\Giftcard::class)
        ->decrementGiftcardValue((string) $args[0], (float) $args[1]),
    'rewardPoints' => model(App\Models\Customer::class)
        ->adjustRewardPoints((int) $args[0], (float) $args[1]),
    'itemQuantity' => model(App\Models\Item_quantity::class)
        ->changeQuantity((int) $args[0], (int) $args[1], (float) $args[2]),
    default => throw new InvalidArgumentException("Unknown race method: {$method}"),
};

echo $result ? '1' : '0';
