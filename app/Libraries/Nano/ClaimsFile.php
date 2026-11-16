<?php

declare(strict_types=1);

namespace App\Libraries\Nano;

/**
 * The durable claim ledger for Nano payments: one send block credits one sale,
 * once, and the record survives a restart.
 *
 * A confirmed Nano send stays valid forever and its hash is public, so replay
 * protection cannot live in memory: a process restart that forgot its claims
 * would let every old block be presented again. Each claim is appended and
 * fsynced to a file under a directory the operator configures, and every claim
 * re-reads that file, so the "block hash -> sale" record outlives the PHP
 * worker. The read, the check and the append happen under ONE exclusive lock,
 * so two workers presenting the same block at the same moment cannot both win.
 * Pure: no dependency and no network, so it unit-tests on its own.
 *
 * File format: one `HASH\tORDER\n` line per claim; the hash is upper-case so a
 * re-read in another case is the same block.
 */
final class ClaimsFile
{
    public function __construct(private readonly string $path)
    {
    }

    /**
     * Record that $blockHash settles $orderId.
     *
     * @return bool true when this is the first time the block is credited;
     *              false when the same block was already recorded for the same
     *              order (idempotent - a retried add is not a new claim)
     *
     * @throws NanoException when the block was already spent on another order,
     *                       or the order was already paid by another block -
     *                       neither is ever credited
     */
    public function claim(string $blockHash, string $orderId): bool
    {
        $hash = strtoupper(trim($blockHash));
        if (preg_match('/^[0-9A-F]{64}$/', $hash) !== 1) {
            throw new NanoException('A Nano block hash is 64 hex characters.');
        }
        if ($orderId === '' || preg_match('/[\x00-\x1F\x7F]/', $orderId) === 1) {
            // A tab or a newline in an order id could forge a second claim line.
            throw new NanoException('A Nano order id must be non-empty and free of control characters.');
        }

        $handle = $this->open();

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new NanoException('The Nano claims file is locked.');
            }

            [$byHash, $byOrder] = $this->read($handle);

            if (isset($byHash[$hash])) {
                if ($byHash[$hash] === $orderId) {
                    return false;
                }

                throw new NanoException('This Nano block was already used to pay another sale.');
            }

            if (isset($byOrder[$orderId]) && $byOrder[$orderId] !== $hash) {
                throw new NanoException('This sale was already paid by another Nano block.');
            }

            fseek($handle, 0, SEEK_END);
            fwrite($handle, $hash . "\t" . $orderId . "\n");
            fflush($handle);
            // fsync when the runtime offers it, so the claim is on disk before
            // the sale is released and a crash cannot forget it.
            if (function_exists('fsync')) {
                @fsync($handle);
            }

            return true;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    public function isClaimed(string $blockHash): bool
    {
        if (!is_file($this->path)) {
            return false;
        }

        $handle = $this->open();

        try {
            flock($handle, LOCK_SH);
            [$byHash] = $this->read($handle);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }

        return isset($byHash[strtoupper(trim($blockHash))]);
    }

    /**
     * @return resource
     */
    private function open()
    {
        $dir = dirname($this->path);
        if (!is_dir($dir) && !mkdir($dir, 0o700, true) && !is_dir($dir)) {
            throw new NanoException('The Nano claims directory could not be created.');
        }

        // c+: create when missing, never truncate, read and write.
        $handle = fopen($this->path, 'c+b');
        if ($handle === false) {
            throw new NanoException('The Nano claims file could not be opened.');
        }

        return $handle;
    }

    /**
     * @param resource $handle
     *
     * @return array{0: array<string, string>, 1: array<string, string>}
     */
    private function read($handle): array
    {
        $byHash = [];
        $byOrder = [];

        rewind($handle);
        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");
            if ($line === '') {
                continue;
            }
            $parts = explode("\t", $line, 2);
            if (count($parts) !== 2) {
                continue;
            }
            [$hash, $order] = $parts;
            $byHash[$hash] = $order;
            $byOrder[$order] = $hash;
        }

        return [$byHash, $byOrder];
    }
}
