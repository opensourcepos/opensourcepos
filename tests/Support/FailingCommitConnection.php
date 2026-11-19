<?php

namespace Tests\Support;

use CodeIgniter\Database\MySQLi\Connection;
use Config\Database;

/**
 * Real connection whose outermost commit reports failure without committing,
 * matching CI4's behaviour when a commit fails: the transaction stays open
 * until the caller rolls it back.
 */
class FailingCommitConnection extends Connection
{
    public bool $failCommit = true;

    public static function create(): self
    {
        return new self((array)config(Database::class)->tests);
    }

    public function silenceQueryErrors(): static
    {
        $this->DBDebug = false;

        return $this;
    }

    public function transCommit(): bool
    {
        if ($this->failCommit && $this->transDepth === 1) {
            return false;
        }

        return parent::transCommit();
    }
}
