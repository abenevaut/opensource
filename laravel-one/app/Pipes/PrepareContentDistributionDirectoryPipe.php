<?php

namespace App\Pipes;

use App\GeneratorSettings;

class PrepareContentDistributionDirectoryPipe extends PipeAbstract
{
    public function handle(GeneratorSettings $generatorSettings, \Closure $next): GeneratorSettings
    {
        if (!is_dir($this->path('dist'))) {
            mkdir($this->path('dist'));
        }

        return $next($generatorSettings);
    }
}
