<?php

namespace App\Process;

use App\Domain\Pages\Pages\Page;
use Symfony\Component\Process\Process;

class GeneratePageProcess extends Process
{
    public function __construct(string $page)
    {
        parent::__construct([
            'php',
            LARAVEL_ONE_BINARY,
            'generate:page',
            $page,
        ]);
    }
}
