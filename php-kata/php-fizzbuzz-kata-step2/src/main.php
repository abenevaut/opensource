<?php

require './vendor/autoload.php';

use App\FizzBuzz;

$fizzBuzz = new FizzBuzz();

try {
    $lines = $fizzBuzz->countTo100();

    foreach ($lines as $line) {
        echo $line . PHP_EOL;
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
