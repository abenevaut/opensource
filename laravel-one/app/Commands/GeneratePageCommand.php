<?php

namespace App\Commands;

use App\Domain\Pages\Pages\Page;
use App\Pipes\PrepareContentDistributionDirectoryPipe;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Yaml\Yaml;

class GeneratePageCommand extends Command
{
    protected $signature = 'generate:page
        {page : Page to generate}';

    protected $description = 'Generate static web page';

    public function handle(): bool
    {
        /** @var Page $page */
        $page = json_decode($this->argument('page'), true);

        $page = new Page(...$page);

        app(Pipeline::class)
            ->send($page)
            ->through([
                PrepareContentDistributionDirectoryPipe::class,
            ])
            ->thenReturn()
        ;

        return self::SUCCESS;
    }
}
