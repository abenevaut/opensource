<?php

namespace App;

class FizzBuzz
{
    public function __construct(protected Database $database) {}

    public function transformFromDatabase(): array
    {
        $results = [];
        $numbers = $this->database->getNumbers();

        foreach ($numbers as $number) {
            $results[$number] = $this->doFizzBuzz($number);
        }

        return $results;
    }

    public function countTo100(): array
    {
        $results = [];

        for ($i = 1; $i <= 100; ++$i) {
            $results[$i] = $this->doFizzBuzz($i);
        }

        return $results;
    }

    public function doModuloThree(int $i): string
    {
        if (($i % 3) === 0) {
            return 'Fizz';
        }

        return '';
    }

    public function doModuloFive(int $i): string
    {
        if (($i % 5) === 0) {
            return 'Buzz';
        }

        return '';
    }

    public function doFizzBuzz(int $i): string
    {
        $result = $this->doModuloThree($i);
        $result .= $this->doModuloFive($i);

        if (empty($result)) {
            return $i;
        }

        return $result;
    }
}