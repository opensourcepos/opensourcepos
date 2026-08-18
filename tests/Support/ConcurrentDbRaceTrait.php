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

    /**
     * Races two model-method invocations against each other by running each
     * in its own PHP CLI process (tests/Support/RaceWorker.php), so the two
     * calls hit the database over genuinely separate connections at the same
     * time instead of running sequentially on one connection.
     *
     * @return array{0: bool, 1: bool} return value of the model method for [call1, call2]
     */
    protected function raceTwoProcesses(string $method, array $args1, array $args2): array
    {
        $workerScript = __DIR__ . '/RaceWorker.php';
        $php          = PHP_BINARY;

        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $command1 = array_merge([$php, $workerScript, $method], array_map('strval', $args1));
        $command2 = array_merge([$php, $workerScript, $method], array_map('strval', $args2));

        $process1 = proc_open($command1, $descriptorSpec, $pipes1, ROOTPATH);
        $process2 = proc_open($command2, $descriptorSpec, $pipes2, ROOTPATH);

        fclose($pipes1[0]);
        fclose($pipes2[0]);

        $output1 = stream_get_contents($pipes1[1]);
        $error1  = stream_get_contents($pipes1[2]);
        fclose($pipes1[1]);
        fclose($pipes1[2]);
        $exitCode1 = proc_close($process1);

        $output2 = stream_get_contents($pipes2[1]);
        $error2  = stream_get_contents($pipes2[2]);
        fclose($pipes2[1]);
        fclose($pipes2[2]);
        $exitCode2 = proc_close($process2);

        if ($exitCode1 !== 0) {
            throw new \RuntimeException("race_worker.php (call 1) exited with {$exitCode1}: {$error1}");
        }

        if ($exitCode2 !== 0) {
            throw new \RuntimeException("race_worker.php (call 2) exited with {$exitCode2}: {$error2}");
        }

        return [trim($output1) === '1', trim($output2) === '1'];
    }
}
