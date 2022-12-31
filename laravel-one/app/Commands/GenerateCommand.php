<?php

namespace App\Commands;

use abenevaut\Infrastructure\Console\ProcessPoolCommandAbstract;
use App\Domain\Pages\Sitemaps\Services\Sitemap;
use App\GeneratorSettings;
use App\Pipes\PrepareCacheDirectoryPipe;
use App\Pipes\PrepareContentPipe;
use App\Pipes\PrepareDistributionDirectoryPipe;
use App\Pipes\ValidateContentDirectoryPipe;
use App\Pipes\ValidateThemeDirectoryPipe;
use Illuminate\Pipeline\Pipeline;

class GenerateCommand extends ProcessPoolCommandAbstract
{
    protected $signature = 'generate
        {url : Website base URL, starting with `https://`}
        {--concurrency=4 : Process pool concurrency}';

    protected $description = 'Generate static web pages';

    public function title(): string
    {
        return "{$this->getQueueLength()} pages to generate";
    }

    public function boot(): self
    {
//        config()->set('content.fallback_lang', 'en');
//        config()->set('content.langs', ['en', 'fr']);
        // dd(LARAVEL_ONE_BINARY);
        // dd(config('content.langs'));

        $generatorSettings = new GeneratorSettings(
            $this->argument('url'),
            [
                Sitemap::create(),
            ]
        );

        $pages = app(Pipeline::class)
            ->send($generatorSettings)
            ->through([
                ValidateContentDirectoryPipe::class,
                ValidateThemeDirectoryPipe::class,
                PrepareContentPipe::class,
                PrepareCacheDirectoryPipe::class,
                PrepareDistributionDirectoryPipe::class,
            ])
            ->thenReturn();

        $this->push($pages->processes);

        foreach ($generatorSettings->plugins as $plugin) {
            $plugin->generate();
        }

        return $this;
    }

    protected function defaultConcurrency(): int
    {
        return $this->option('concurrency') ?? 4;
    }
}
