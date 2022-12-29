<?php

namespace App\Commands;

use App\Builders\PageBuilder;
use App\Process\GeneratePageProcess;
use App\Services\Sitemap;
use App\Services\ProcessPoolService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Spatie\Sitemap\Tags\Url;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Yaml\Yaml;

class GenerateCommand extends Command
{
    protected $signature = 'generate
        {url : Website base URL, starting with `https://`}
        {--concurrency=4 : Process pool concurrency}';

    protected $description = 'Generate static web pages';

    public function handle(): bool
    {
        try {
            config()->set('content.fallback_lang', 'en');
            config()->set('content.langs', ['en', 'fr']);

            // dd(LARAVEL_ONE_BINARY);
            // dd(config('content.langs'));

            $pageBuilder = new PageBuilder();
            $pageBuilder->prepare();

            config()->set('view.compiled', $pageBuilder->getCacheDirectory());
            config()->set('view.paths', array_merge(config('view.paths'), [$pageBuilder->getThemeDirectory()]));

            $this->output->title($pageBuilder->getNumberOfFiles().' pages to generate');
            $bar = $this->output->createProgressBar(1);
            $bar->setFormat('debug');

            $processPreparationBar = $this->output->createProgressBar($pageBuilder->getNumberOfFiles());
            $processPreparationBar->setFormat('debug');

            $processPool = $pageBuilder->generate($this->argument('url'), $bar, 4, fn(ProgressBar &$processPreparationBar) => $processPreparationBar->advance());

            $processPreparationBar->finish();
            $bar->advance(); // hack to show progressbar

            $processPool->start();

            $bar->finish();

            $this->newLine();
            $this->info('All Processes Done!');
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
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
