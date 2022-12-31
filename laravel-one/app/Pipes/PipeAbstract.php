<?php

namespace App\Pipes;

class PipeAbstract
{
    protected function path($path)
    {
        return getcwd() . DIRECTORY_SEPARATOR . $path;
    }
}
