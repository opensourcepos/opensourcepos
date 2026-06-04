<?php

namespace App\Commands;

use App\Libraries\SecondaryCurrencyFeedLib;
use App\Models\Appconfig;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\OSPOS;
use Throwable;

class RefreshSecondaryCurrency extends BaseCommand
{
    protected $group = 'OSPOS';
    protected $name = 'currency:refresh-secondary';
    protected $description = 'Refreshes the configured secondary currency rate from the live feed.';
    protected $usage = 'currency:refresh-secondary [--force]';

    public function run(array $params)
    {
        $force = in_array('--force', $params, true) || in_array('-f', $params, true);
        $config = config(OSPOS::class)->settings;
        $autoEnabled = !empty($config['secondary_currency_auto_enabled']);

        if (!$force && !$autoEnabled) {
            CLI::write(lang('Config.secondary_currency_auto_refresh_disabled_force'), 'yellow');
            return;
        }

        $service = new SecondaryCurrencyFeedLib();
        $result = $service->refreshRate($config, $force);

        if (empty($result['success'])) {
            CLI::write($result['message'] ?? lang('Config.secondary_currency_refresh_failed'), 'red');
            $this->persistStatus(false, null, (string) ($result['message'] ?? lang('Config.secondary_currency_refresh_failed')));
            return;
        }

        $rate = (float) $result['rate'];
        $now = date('Y-m-d H:i:s');
        $this->persistStatus(true, $rate, '', $now);

        CLI::write(lang('Config.secondary_currency_refresh_successful'), 'green');
        CLI::write('Rate: ' . $rate);
        CLI::write('Feed: ' . ($result['feed_url'] ?? ''));
    }

    private function persistStatus(bool $success, ?float $rate, string $error, ?string $syncedAt = null): void
    {
        try {
            $appconfig = model(Appconfig::class);

            $payload = [
                'secondary_currency_last_error' => $success ? '' : $error,
            ];

            if ($rate !== null) {
                $payload['secondary_currency_rate'] = $rate;
            }

            if ($syncedAt !== null) {
                $payload['secondary_currency_last_synced_at'] = $syncedAt;
            }

            $appconfig->batch_save($payload);
        } catch (Throwable $throwable) {
            CLI::write(lang('Config.secondary_currency_feed_request_failed', [$throwable->getMessage()]), 'red');
        }
    }
}
