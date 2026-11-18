<?php

namespace Tests\Support\Jobs;

use App\Jobs\ImportRowJob;

class BatchProgressTestJob extends ImportRowJob
{
    public static bool $rowSucceeds = true;

    public function process(): bool
    {
        return $this->processBatchRow(static fn (): bool => self::$rowSucceeds);
    }
}
