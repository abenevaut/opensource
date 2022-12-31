<?php

namespace App\Processes;

use App\Domain\Pages\Pages\Page;
use Symfony\Component\Process\Process;

class GeneratePageProcess extends Process
{
    public function __construct(Page $page)
    {
        parent::__construct([
            'php',
            LARAVEL_ONE_BINARY,
            'generate:page',
            // @todo xABE: Should be transformed to $page->toJson()
            json_encode([
                'file' => $page->file,
                'distUrl' => $page->distUrl,
                'distUri' => $page->distUri,
                'content' => [],
            ]),
        ]);
    }
}
