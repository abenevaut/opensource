<?php

namespace App\Builders;

use App\Process\GeneratePageProcess;
use App\Services\ProcessPoolService;
use App\Services\Sitemap;
use Illuminate\Support\Str;
use Spatie\Sitemap\Tags\Url;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Yaml\Yaml;

class PageBuilder
{
    protected string $contentDirectory;

    protected string $themeDirectory;

    protected string $cacheDirectory;

    protected array $files;

    protected int $numberOfFiles = 0;

    protected Sitemap $sitemap;

    /**
     * @return int
     */
    public function getNumberOfFiles(): int
    {
        return $this->numberOfFiles;
    }

    /**
     * @return string
     */
    public function getCacheDirectory(): string
    {
        return $this->cacheDirectory;
    }

    /**
     * @return string
     */
    public function getThemeDirectory(): string
    {
        return $this->themeDirectory;
    }

    /**
     * @throws \Exception
     */
    public function prepare(): void
    {
        $this
            ->validateContentDirectory()
            ->validateThemeDirectory()
            ->prepareContent()
            ->prepareCacheDirectory()
            ->prepareDistributionDirectory()
            ->createSitemap()
        ;
    }

    public function generate(string $url, ProgressBar &$progressBar = null, int $concurrency = 4, ?\Closure $closure = null): ProcessPoolService
    {
        $processPool = new ProcessPoolService($progressBar);
        $processPool->concurrency($concurrency);

        foreach ($this->files as $file) {
            $content = Yaml::parse(file_get_contents($file));
            $dirPath = Str::replace('content', 'dist', dirname($file));

            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0777, true);
            }

            $distPath = Str::remove($this->path('content/'), $file);
            $distPath = Str::replace('yml', 'html', $distPath);

            if (!array_key_exists('sitemap', $content) || $content['sitemap'] === true) {
                $url = URL::create("{$url}/{$distPath}")
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.1);
                $this->sitemap->add($url);
            }

            $processPool->addProcess(new GeneratePageProcess($file));

            $closure($progressBar);
        }

        $progressBar->setMaxSteps($processPool->queueCount() + 1);

        $this->sitemap->writeToFile($this->path('dist/sitemap.xml'));

        return $processPool;
    }

    protected function createSitemap()
    {
        $this->sitemap = Sitemap::create();

        return $this;
    }

    /**
     * @throws \Exception
     */
    protected function prepareContent(): self
    {
        $this->files = array_merge(
            glob("{$this->contentDirectory}/*.yml"),
            glob("{$this->contentDirectory}/**/*.yml"),
        );

        $this->numberOfFiles = count($this->files);

        if ($this->numberOfFiles === 0) {
             throw new \Exception('`content` directory does not contain any content files (`*.yml`)!');
        }

        return $this;
    }

    /**
     * @throws \Exception
     */
    protected function prepareDistributionDirectory(): self
    {
        if (!is_dir($this->path('dist'))) {
            mkdir($this->path('dist'));
        }

        return $this;
    }

    /**
     * @throws \Exception
     */
    protected function prepareCacheDirectory(): self
    {
        $this->cacheDirectory = $this->path('.cache');

        if (!is_dir($this->cacheDirectory)) {
            mkdir($this->cacheDirectory);
        }

        return $this;
    }

    /**
     * @throws \Exception
     */
    protected function validateContentDirectory(): self
    {
        $this->contentDirectory = $this->path('content');

        if (!is_dir($this->contentDirectory)) {
            throw new \Exception('`content` directory not found!');
        }

        return $this;
    }

    /**
     * @throws \Exception
     */
    protected function validateThemeDirectory(): self
    {
        $this->themeDirectory = $this->path('theme');

        if (!is_dir($this->themeDirectory)) {
            throw new \Exception('`theme` directory not found!');
        }

        return $this;
    }

    private function path($path)
    {
        return getcwd() . DIRECTORY_SEPARATOR . $path;
    }
}
