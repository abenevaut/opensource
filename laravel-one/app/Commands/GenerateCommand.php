<?php

namespace App\Commands;

use abenevaut\Infrastructure\Console\ProcessPoolCommandAbstract;
use App\Domain\Pages\Pages\Builders\PageBuilder;
use Illuminate\Console\Scheduling\Schedule;

class GenerateCommand extends ProcessPoolCommandAbstract
{
    protected $signature = 'generate
        {url : Website base URL, starting with `https://`}
        {--concurrency=4 : Process pool concurrency}';

    protected $description = 'Generate static web pages';

    protected function defaultConcurrency(): int
    {
        return $this->option('concurrency') ?? 4;
    }

    public function boot(): self
    {
        config()->set('content.fallback_lang', 'en');
        config()->set('content.langs', ['en', 'fr']);

        // dd(LARAVEL_ONE_BINARY);
        // dd(config('content.langs'));

        $pageBuilder = new PageBuilder();
        $pageBuilder->prepare();

        foreach ($pageBuilder->generate($this->argument('url')) as $process) {
            $this->push($process);
        }

        config()->set('view.compiled', $pageBuilder->getCacheDirectory());
        config()->set('view.paths', array_merge(config('view.paths'), [$pageBuilder->getThemeDirectory()]));

        return $this;
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function schedule(Schedule $schedule)
    {
        // $schedule->command(static::class)->everyMinute();
    }


}
