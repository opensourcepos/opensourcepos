<?php

namespace App\Filters;

use CodeIgniter\I18n\Time;
use CodeIgniter\Tasks\TaskLog;
use CodeIgniter\Tasks\TaskRunner;
use Throwable;

/**
 * TaskRunner variant that stops starting new tasks once a deadline has
 * passed. CI4's TaskRunner::run() has no extension point to check this
 * between tasks, so the loop is duplicated here from the vendor
 * implementation. A task already in progress cannot be interrupted
 * mid-execution (PHP has no portable, FPM-safe preemption mechanism), so
 * this only bounds how many *additional* tasks are started after the
 * deadline, not the runtime of a task that was already running.
 *
 * $taskMaxSeconds is a soft, log-only budget: if a single task's run()
 * takes longer than this, a warning is logged after the fact. It cannot
 * stop or interrupt the task for the same preemption reason above.
 */
class BoundedTaskRunner extends TaskRunner
{
    private float $deadline;
    private float $taskMaxSeconds;

    public function __construct(float $deadline, float $taskMaxSeconds)
    {
        parent::__construct();

        $this->deadline = $deadline;
        $this->taskMaxSeconds = $taskMaxSeconds;
    }

    public function run()
    {
        $tasks = $this->scheduler->getTasks();

        if ($tasks === []) {
            return;
        }

        foreach ($tasks as $task) {
            if (microtime(true) >= $this->deadline) {
                log_message('info', 'JobRunner: deadline reached, skipping remaining tasks.');
                break;
            }

            if ($this->only !== [] && !in_array($task->name, $this->only, true)) {
                continue;
            }

            if (!$task->shouldRun($this->testTime) && $this->only === []) {
                continue;
            }

            $error = null;
            $start = Time::now();
            $startMicrotime = microtime(true);
            $output = null;

            $this->cliWrite('Processing: ' . ($task->name ?: 'Task'), 'green');

            try {
                $output = $task->run();

                $elapsed = microtime(true) - $startMicrotime;

                if ($elapsed > $this->taskMaxSeconds) {
                    log_message('warning', sprintf(
                        'JobRunner: task "%s" took %.2fs, exceeding jobs_task_max_seconds (%.2fs).',
                        $task->name ?: 'Task',
                        $elapsed,
                        $this->taskMaxSeconds
                    ));
                }

                $this->cliWrite('Executed: ' . ($task->name ?: 'Task'), 'cyan');
            } catch (Throwable $e) {
                $this->cliWrite('Failed: ' . ($task->name ?: 'Task'), 'red');

                log_message('error', $e->getMessage(), $e->getTrace());
                $error = $e;
            } finally {
                $taskLog = new TaskLog([
                    'task' => $task,
                    'output' => $output,
                    'runStart' => $start,
                    'runEnd' => Time::now(),
                    'error' => $error,
                ]);

                $this->updateLogs($taskLog);
            }
        }
    }
}
