<?php

namespace App\Commands;

use App\Domain\Pages\Pages\Page;
use App\Pipes\FindOrCreatePageDistributionDirectoryPipe;
use App\Pipes\WritePagePipe;
use Illuminate\Pipeline\Pipeline;
use LaravelZero\Framework\Commands\Command;

class GeneratePageCommand extends Command
{
    protected $signature = 'generate:page
        {page : Page to generate}';

    protected $description = 'Generate static web page';

    public function handle(): bool
    {
        $page = json_decode($this->argument('page'), true);
        $page = new Page(...$page);

        app(Pipeline::class)
            ->send($page)
            ->through([
                FindOrCreatePageDistributionDirectoryPipe::class,
                WritePagePipe::class,
            ])
            ->thenReturn()
        ;

        return self::SUCCESS;
    }
}
