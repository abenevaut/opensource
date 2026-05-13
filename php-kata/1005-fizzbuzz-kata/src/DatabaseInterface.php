<?php

namespace App;

interface DatabaseInterface
{
    /**
     * return a list of numbers
     *
     * @return array
     */
    public function getNumbers(): array;
}
