<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
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
        $file = $this->argument('file');

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
