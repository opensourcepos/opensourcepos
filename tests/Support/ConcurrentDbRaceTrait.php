<?php

namespace Tests\Support;

/**
 * Races two raw SQL statements against each other over two independent
 * mysqli connections, using MYSQLI_ASYNC so both are in flight before either
 * resolves. This exercises real database row-level locking instead of
 * simulating concurrency with two sequential calls on one connection.
 */
trait ConcurrentDbRaceTrait
{
    /**
     * @return array{0: int, 1: int} affected_rows for [sql1, sql2]
     */
    protected function raceTwoUpdates(string $sql1, string $sql2): array
    {
        $config = config('Database')->tests;

        $link1 = mysqli_connect($config['hostname'], $config['username'], $config['password'], $config['database'], $config['port']);
        $link2 = mysqli_connect($config['hostname'], $config['username'], $config['password'], $config['database'], $config['port']);

        mysqli_query($link1, $sql1, MYSQLI_ASYNC);
        mysqli_query($link2, $sql2, MYSQLI_ASYNC);

        $pending = [$link1, $link2];

        while ($pending !== []) {
            $read = $pending;
            $error = $pending;
            $reject = $pending;

            if (mysqli_poll($read, $error, $reject, 5) === false) {
                break;
            }

            foreach (array_merge($read, $error, $reject) as $link) {
                mysqli_reap_async_query($link);
                $pending = array_filter($pending, static fn ($pendingLink) => $pendingLink !== $link);
            }
        }

        $affectedRows1 = mysqli_affected_rows($link1);
        $affectedRows2 = mysqli_affected_rows($link2);

        mysqli_close($link1);
        mysqli_close($link2);

        return [$affectedRows1, $affectedRows2];
    }
}
