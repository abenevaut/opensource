<?php

namespace App\Services;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Process\Process;

class ProcessPoolService
{
    protected array      $queuePool;
    protected array      $runningPool;
    protected int        $queuePoolCount;
    protected int        $runningPoolCount;
    private int          $runningCount;
    private int          $concurrency;
    private int          $increment;
    private ?ProgressBar $progressBar;
    private array        $outputs;

    public function __construct(ProgressBar &$progressBar = null)
    {
        $this->queuePool = [];
        $this->runningPool = [];
        $this->queuePoolCount = 0;
        $this->runningPoolCount = 0;
        $this->progressBar = $progressBar;
        $this->runningCount = 0;
        $this->concurrency = 4;
        $this->increment = 1;
        $this->outputs = [];
    }

    /**
     * @return int
     */
    public function queueCount(): int
    {
        return count($this->queuePool);
    }

    /**
     * @param  int  $concurrency
     */
    public function concurrency(int $concurrency)
    {
        $this->concurrency = $concurrency;
    }

    /**
     * @param  int  $increment
     */
    public function increment(int $increment)
    {
        $this->increment = $increment;
    }

    /**
     * @return int
     */
    public function getConcurrency(): int
    {
        return $this->concurrency;
    }

    /**
     * @return array
     */
    public function getOutputs(): array
    {
        return $this->outputs;
    }

    /**
     * @param  callable  $callback
     * @return mixed
     */
    public function then(callable $callback)
    {
        $callback();

        return $this;
    }

    /**
     * @return bool
     */
    public function hasProcess(): bool
    {
        return $this->queuePoolCount > 0 || $this->runningPoolCount > 0;
    }

    public function start()
    {
        $this->startsAt = microtime(true);

        $this->runningPoolCount = count($this->runningPool);
        $this->queuePoolCount = count($this->queuePool);

        // Tant qu'il existe des process dans les pools on boucle
        while ($this->queuePoolCount > 0 || $this->runningPoolCount > 0) {
            // Si le nombre de processus en cours n'atteint pas le nombre de concurrency
            // et qu'on a des processus en attente dans la queuePool
            // on ajoute dans la runningPool
            if ($this->queuePoolCount > 0 && $this->runningCount < $this->concurrency) {
                /** @var Process $process */
                $process = array_shift($this->queuePool);
                $process->start();

                $this->queuePoolCount--;
                $this->runningCount++;
                $this->runningPoolCount++;

                $this->runningPool[] = $process;
            }

            // Si il existe des processus dans la runningPool
            if ($this->runningPoolCount > 0) {
                // on vérifie si dans la runningPool des processus sont terminés
                foreach ($this->runningPool as $key => $process) {
                    // si un processus est terminé, on l'enlève de la runningPool
                    if ($process->isRunning() === false) {
                        $this->advanceBar();

                        $outputType = ($process->getExitCode() === 0) ? 'info' : 'error';
                        $this->outputs[$outputType][] = $process->getOutput();

                        $this->runningCount--;
                        $this->runningPoolCount--;

                        unset($this->runningPool[$key]);
                    }
                }
            }

            usleep(10000);
        }

        return $this;
    }

    /**
     * @param  Process  $process
     */
    public function addProcess(Process $process): void
    {
        $this->queuePool[] = $process;
        $this->queuePoolCount++;
    }

    public function reset(): void
    {
        $this->queuePool = [];
        $this->queuePoolCount = 0;

        $this->runningPool = [];
        $this->runningPoolCount = 0;
    }

    private function advanceBar()
    {
        if ($this->progressBar === null) {
            return;
        }

        $this->progressBar->advance($this->increment);
    }
}
