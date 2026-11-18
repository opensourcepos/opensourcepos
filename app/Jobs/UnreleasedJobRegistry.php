<?php

namespace App\Jobs;

use RuntimeException;

/**
 * File-backed list of reserved job ids that a worker popped but could not put
 * back. Every read-modify-write holds an exclusive file lock, and claim()
 * empties the list in the same locked step, so two workers can never receive
 * the same id and ids recorded concurrently are never lost.
 */
final class UnreleasedJobRegistry
{
    public function __construct(private readonly string $path)
    {
    }

    /**
     * @param int[] $ids
     */
    public function record(array $ids): void
    {
        $this->update(static fn (array $current): array => array_values(array_unique(array_merge($current, array_map('intval', $ids)))));
    }

    /**
     * Removes and returns every recorded id; the caller now owns them.
     *
     * @return int[]
     */
    public function claim(): array
    {
        $claimed = [];

        $this->update(static function (array $current) use (&$claimed): array {
            $claimed = $current;

            return [];
        });

        return $claimed;
    }

    private function update(callable $change): void
    {
        $directory = dirname($this->path);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Cannot create directory for unreleased job registry: ' . $directory);
        }

        $handle = fopen($this->path, 'c+');

        if ($handle === false) {
            throw new RuntimeException('Cannot open unreleased job registry: ' . $this->path);
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new RuntimeException('Cannot lock unreleased job registry: ' . $this->path);
            }

            $decoded = json_decode((string)stream_get_contents($handle), true);
            $next = $change(is_array($decoded) ? array_map('intval', $decoded) : []);

            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($next));
            fflush($handle);
            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }
    }
}
