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
        {file : Page to generate}';

    protected $description = 'Generate static web page';

    public function handle(): bool
    {
        /** @var Page $page */
        $page = $this->argument('page');

        app(Pipeline::class)
            ->send($page)
            ->through([
                PrepareContentDistributionDirectoryPipe::class,
            ])
            ->thenReturn()
        ;








        $content = Yaml::parse(file_get_contents($file));
        $dirPath = Str::replace('content', 'dist', dirname($file));

        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0777, true);
        }

        $distPath = Str::remove($this->path('content/'), $file);
        $distPath = Str::replace('yml', 'html', $distPath);

        try {
            $page = View::make($content['view'], $content);

            file_put_contents($this->path("dist/{$distPath}"), $page);
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }

        return self::SUCCESS;
    }
}
