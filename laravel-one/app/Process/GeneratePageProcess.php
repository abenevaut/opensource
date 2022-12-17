<?php

namespace App\Process;

use Symfony\Component\Process\Process;

class GeneratePageProcess extends Process
{
    public function __construct($file)
    {
        parent::__construct([
            'php',
            LARAVEL_ONE_BINARY,
            'generate:page',
            $file,
        ]);
    }
}
