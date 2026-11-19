<?php

namespace App\Jobs;

use RuntimeException;

/**
 * File-backed map of reserved job ids that a worker popped but could not put
 * back, each with the fence (the row's available_at when it was stranded) that
 * proves the reservation is still the stranded one. Every change holds an
 * exclusive file lock.
 *
 * claim() does not delete the ids: it moves them to a lease held by the
 * claiming owner, so no other worker receives them while the owner releases
 * them. complete() drops the lease once the release succeeded and abandon()
 * returns the ids to the list if it did not. A lease that is never completed
 * (the owner stopped) expires and its ids are handed to the next claimer, so
 * an id is never lost and never held by two live owners at once.
 */
final class UnreleasedJobRegistry
{
    public function __construct(
        private readonly string $path,
        private readonly int $leaseSeconds = 300,
    ) {
    }

    /**
     * @param array<int, int> $fences Job id => fence.
     */
    public function record(array $fences): void
    {
        $this->update(static function (array $state) use ($fences): array {
            $state['pending'] = array_replace($state['pending'], self::intMap($fences));

            return $state;
        });
    }

    /**
     * Leases every pending id, plus the ids of expired leases, to $owner.
     *
     * @return array<int, int> Job id => fence.
     */
    public function claim(string $owner, ?int $now = null): array
    {
        $now ??= time();
        $claimed = [];

        $this->update(function (array $state) use ($owner, $now, &$claimed): array {
            $ids = $state['pending'];

            foreach ($state['claims'] as $claimOwner => $claim) {
                if ($claim['at'] + $this->leaseSeconds <= $now) {
                    $ids += $claim['ids'];
                    unset($state['claims'][$claimOwner]);
                }
            }

            $claimed = $ids;
            $state['pending'] = [];

            if ($claimed !== []) {
                $state['claims'][$owner] = ['ids' => $claimed, 'at' => $now];
            }

            return $state;
        });

        return $claimed;
    }

    public function complete(string $owner): void
    {
        $this->update(static function (array $state) use ($owner): array {
            unset($state['claims'][$owner]);

            return $state;
        });
    }

    public function abandon(string $owner): void
    {
        $this->update(static function (array $state) use ($owner): array {
            if (isset($state['claims'][$owner])) {
                $state['pending'] = array_replace($state['claims'][$owner]['ids'], $state['pending']);
                unset($state['claims'][$owner]);
            }

            return $state;
        });
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

            $next = $change($this->normalize(json_decode((string)stream_get_contents($handle), true)));

            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($next));
            fflush($handle);
            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }
    }

    private function normalize(mixed $decoded): array
    {
        $state = ['pending' => [], 'claims' => []];

        if (!is_array($decoded)) {
            return $state;
        }

        $state['pending'] = self::intMap((array)($decoded['pending'] ?? []));

        foreach ((array)($decoded['claims'] ?? []) as $owner => $claim) {
            if (is_array($claim) && isset($claim['at'], $claim['ids'])) {
                $state['claims'][(string)$owner] = ['ids' => self::intMap((array)$claim['ids']), 'at' => (int)$claim['at']];
            }
        }

        return $state;
    }
    /**
     * Entries from the earlier list-of-ids format carry no fence and are dropped.
     *
     * @return array<int, int>
     */
    private static function intMap(array $map): array
    {
        if (array_is_list($map)) {
            return [];
        }

        return array_map('intval', array_combine(array_map('intval', array_keys($map)), $map));
    }
}
