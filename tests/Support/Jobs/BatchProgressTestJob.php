<?php

namespace Tests\Support\Jobs;

use App\Jobs\ImportRowJob;
use Closure;

class BatchProgressTestJob extends ImportRowJob
{
    public static bool $rowSucceeds = true;
    public static ?Closure $onRow = null;

    public function process(): bool
    {
        return $this->processBatchRow(static function (): bool {
            if (self::$onRow !== null) {
                (self::$onRow)();
            }

            return self::$rowSucceeds;
        });
    }
}
