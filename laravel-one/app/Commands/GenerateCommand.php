<?php

namespace App\Commands;

use App\Process\GeneratePageProcess;
use App\Services\Sitemap;
use App\Services\ProcessPoolService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Spatie\Sitemap\Tags\Url;
use Symfony\Component\Yaml\Yaml;

class GenerateCommand extends Command
{
    protected $signature = 'generate
        {url : Website base URL, starting with `https://`}
        {--concurrency=4 : Process pool concurrency}';

    protected $description = 'Generate static web pages';

    public function handle(): bool
    {
        if (!is_dir($this->path('content'))) {
            $this->error('`content` directory not found!');

            return self::FAILURE;
        }

        if (!is_dir($this->path('theme'))) {
            $this->error('`theme` directory not found!');

            return self::FAILURE;
        }

        $files = array_merge(
            glob($this->path('content/*.yml')),
            glob($this->path('content/**/*.yml')),
        );

        if (count($files) === 0) {
            $this->error('`content` directory does not contain any content files (`*.yml`)!');

            return self::FAILURE;
        }

        config()->set('view.compiled', $this->path('.cache'));
        config()->set('view.paths', array_merge(config('view.paths'), [$this->path('theme')]));

        config()->set('content.fallback_lang', 'en');
        config()->set('content.langs', ['en', 'fr']);

        //dd(config('content.langs'));


        if (!is_dir($this->path('dist'))) {
            mkdir($this->path('dist'));
        }

        if (!is_dir($this->path('.cache'))) {
            mkdir($this->path('.cache'));
        }

        $sitemap = Sitemap::create();






//        dd(LARAVEL_ONE_BINARY);

        $processCount = count($files);
        $this->output->title($processCount.' pages to generate');

        $bar = $this->output->createProgressBar(1);
        $bar->setFormat('debug');

        $processPool = new ProcessPoolService($bar);
        $processPool->concurrency($this->option('concurrency'));

        $processPreparationBar = $this->output->createProgressBar($processCount);
        $processPreparationBar->setFormat('debug');



        foreach ($files as $file) {
            $content = Yaml::parse(file_get_contents($file));
            $dirPath = Str::replace('content', 'dist', dirname($file));

            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0777, true);
            }

            $distPath = Str::remove($this->path('content/'), $file);
            $distPath = Str::replace('yml', 'html', $distPath);

//            try {
                if (!array_key_exists('sitemap', $content) || $content['sitemap'] === true) {
                    $url = URL::create($this->argument('url') . "/{$distPath}")
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                        ->setPriority(0.1);
                    $sitemap->add($url);
                }

                $processPool->addProcess(new GeneratePageProcess($file));
//            } catch (\Exception $exception) {
//                $this->error($exception->getMessage());
//            }

            $processPreparationBar->advance();
        }

        $processPreparationBar->finish();




        $bar->setMaxSteps($processPool->queueCount() + 1);
        $bar->advance(); // hack to show progressbar

        $processPool->start();

        $bar->finish();

        $this->newLine();
        $this->info('All Processes Done!');





        $sitemap->writeToFile($this->path("dist/sitemap.xml"));

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

    private function path($path)
    {
        return getcwd() . DIRECTORY_SEPARATOR . $path;
    }
}
