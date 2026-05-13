<?php

namespace App;

class Database implements DatabaseInterface
{
    public function getNumbers(): array
    {
        throw new \Exception("DO NOT IMPLEMENT", 501);
    }
}
