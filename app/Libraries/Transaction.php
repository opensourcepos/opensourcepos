<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * CI4 leaves the transaction open when an outermost commit fails
 * (transCommit() returns false without changing the depth), and keeps a
 * failed status flag until it is reset. These helpers finish a transaction
 * and, on failure, roll it back so the connection is usable again.
 */
final class Transaction
{
    /**
     * Completes a transaction opened with transStart().
     *
     * @return bool True only if the transaction was committed.
     */
    public static function complete(BaseConnection $db): bool
    {
        $depth = $db->transDepth;

        if ($db->transComplete()) {
            return true;
        }

        self::recover($db, $depth);

        return false;
    }

    /**
     * Commits a transaction opened with transBegin(), rolling it back if the
     * status flag shows a failed query or the commit itself fails.
     *
     * @return bool True only if the transaction was committed.
     */
    public static function commit(BaseConnection $db): bool
    {
        $depth = $db->transDepth;

        if ($db->transStatus() !== false && $db->transCommit()) {
            return true;
        }

        self::recover($db, $depth);

        return false;
    }

    private static function recover(BaseConnection $db, mixed $depthBefore): void
    {
        if ($db->transDepth === $depthBefore) {
            $db->transRollback();
        }

        if ($db->transDepth === 0) {
            $db->resetTransStatus();
        }
    }
}
