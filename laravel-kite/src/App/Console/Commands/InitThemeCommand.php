<?php

namespace abenevaut\Kite\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InitThemeCommand extends Command
{
    protected $signature = 'kite:init-theme {name : The name of the theme}';

    protected $description = 'Initialize a new theme with Inertia.js and TailwindUI';

    public function __construct(
        protected Filesystem $files
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $themeName = $this->argument('name');

        $this->info("Initializing theme: {$themeName}");

        $this->updateComposerJson($themeName);
        $this->publishTailwindConfig();
        $this->publishLayouts();
        $this->warnAboutRouteRemoval();

        $this->info("Theme {$themeName} initialized successfully!");

        return Command::SUCCESS;
    }

    protected function updateComposerJson(string $themeName): void
    {
        $composerPath = base_path('composer.json');
        $composer = json_decode(file_get_contents($composerPath), true);

        $composer['require']['@abenevaut/tailwindui'] = '1.3.0-rc.6';

        file_put_contents(
            $composerPath,
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
        );

        $this->info('Added @abenevaut/tailwindui to composer.json');
    }

    protected function publishTailwindConfig(): void
    {
        $sourcePath = __DIR__ . '/../../../tailwind.config.cjs';
        $targetPath = base_path('tailwind.config.cjs');

        if ($this->files->exists($sourcePath)) {
            $this->files->copy($sourcePath, $targetPath);
            $this->info('Published tailwind.config.cjs');
        }
    }

    protected function publishLayouts(): void
    {
        $sourceLayoutsPath = __DIR__ . '/../../../resources/js';
        $targetLayoutsPath = resource_path('js');

        if ($this->files->exists($sourceLayoutsPath)) {
            $this->files->copyDirectory($sourceLayoutsPath, $targetLayoutsPath);
            $this->info('Published JS resources (Pages, Components, Layouts)');
        }
    }

    protected function warnAboutRouteRemoval(): void
    {
        $this->warn('IMPORTANT: You must manually remove auth routes from routes/web.php');
        $this->warn('Routes to remove: login, register, logout, forgot-password, reset-password,');
        $this->warn('confirm-password, password.update, verification.send, verification.notice, verification.verify');
        $this->warn('Also remove any "auth" or "guest" middleware groups that wrap these routes');
    }
}