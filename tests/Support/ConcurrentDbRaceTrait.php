<?php

namespace Tests\Support;

use RuntimeException;

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

        try {
            $pending  = [$link1, $link2];
            $deadline = hrtime(true) + 10_000_000_000;

            while ($pending !== []) {
                if (hrtime(true) >= $deadline) {
                    throw new RuntimeException('mysqli_poll failed to resolve pending queries before deadline');
                }

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
        } finally {
            mysqli_close($link1);
            mysqli_close($link2);
        }

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

        $readyFile1 = tempnam(sys_get_temp_dir(), 'race_ready_');
        $readyFile2 = tempnam(sys_get_temp_dir(), 'race_ready_');
        $goFile     = tempnam(sys_get_temp_dir(), 'race_go_');
        unlink($readyFile1);
        unlink($readyFile2);
        unlink($goFile);

        $baseEnv = array_filter($_SERVER, static fn ($value) => is_scalar($value));
        $env1    = array_merge($baseEnv, ['RACE_READY_FILE' => $readyFile1, 'RACE_GO_FILE' => $goFile]);
        $env2    = array_merge($baseEnv, ['RACE_READY_FILE' => $readyFile2, 'RACE_GO_FILE' => $goFile]);

        $process1 = proc_open($command1, $descriptorSpec, $pipes1, ROOTPATH, $env1);
        $process2 = proc_open($command2, $descriptorSpec, $pipes2, ROOTPATH, $env2);

        fclose($pipes1[0]);
        fclose($pipes2[0]);

        stream_set_blocking($pipes1[1], false);
        stream_set_blocking($pipes1[2], false);
        stream_set_blocking($pipes2[1], false);
        stream_set_blocking($pipes2[2], false);

        try {
            $deadline = microtime(true) + 5.0;

            while (!file_exists($readyFile1) || !file_exists($readyFile2)) {
                if (microtime(true) >= $deadline) {
                    $status1 = proc_get_status($process1);
                    $status2 = proc_get_status($process2);

                    if ($status1['running']) {
                        proc_terminate($process1);
                    }

                    if ($status2['running']) {
                        proc_terminate($process2);
                    }

                    fclose($pipes1[1]);
                    fclose($pipes1[2]);
                    fclose($pipes2[1]);
                    fclose($pipes2[2]);
                    proc_close($process1);
                    proc_close($process2);

                    $waiting = array_filter([
                        !file_exists($readyFile1) ? 'call 1' : null,
                        !file_exists($readyFile2) ? 'call 2' : null,
                    ]);

                    throw new RuntimeException('race_worker.php failed to reach readiness barrier: ' . implode(', ', $waiting));
                }

                usleep(1000);
            }

            file_put_contents($goFile, '1');

            $output1 = '';
            $error1  = '';
            $output2 = '';
            $error2  = '';

            $drain = static function () use ($pipes1, $pipes2, &$output1, &$error1, &$output2, &$error2): void {
                $output1 .= stream_get_contents($pipes1[1]);
                $error1  .= stream_get_contents($pipes1[2]);
                $output2 .= stream_get_contents($pipes2[1]);
                $error2  .= stream_get_contents($pipes2[2]);
            };

            $completionDeadline = microtime(true) + 10.0;

            while (true) {
                $drain();

                $status1 = proc_get_status($process1);
                $status2 = proc_get_status($process2);

                if (!$status1['running'] && !$status2['running']) {
                    $drain();
                    break;
                }

                if (microtime(true) >= $completionDeadline) {
                    if ($status1['running']) {
                        proc_terminate($process1);
                    }

                    if ($status2['running']) {
                        proc_terminate($process2);
                    }

                    fclose($pipes1[1]);
                    fclose($pipes1[2]);
                    fclose($pipes2[1]);
                    fclose($pipes2[2]);
                    proc_close($process1);
                    proc_close($process2);

                    $stillRunning = array_filter([
                        $status1['running'] ? 'call 1' : null,
                        $status2['running'] ? 'call 2' : null,
                    ]);

                    throw new RuntimeException('race_worker.php failed to complete before deadline: ' . implode(', ', $stillRunning));
                }

                usleep(1000);
            }

            fclose($pipes1[1]);
            fclose($pipes1[2]);
            $exitCode1 = proc_close($process1);

            fclose($pipes2[1]);
            fclose($pipes2[2]);
            $exitCode2 = proc_close($process2);
        } finally {
            foreach ([$readyFile1, $readyFile2, $goFile] as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }

        if ($exitCode1 !== 0) {
            throw new RuntimeException("race_worker.php (call 1) exited with {$exitCode1}: {$error1}");
        }

        if ($exitCode2 !== 0) {
            throw new RuntimeException("race_worker.php (call 2) exited with {$exitCode2}: {$error2}");
        }

        return [trim($output1) === '1', trim($output2) === '1'];
    }
}
