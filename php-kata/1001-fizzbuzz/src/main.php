<?php

require './vendor/autoload.php';

use App\FizzBuzz;

try {
    $lines = (new FizzBuzz())->countTo100();

    foreach ($lines as $line) {
        echo $line . PHP_EOL;
    }

} catch (Exception $e) {
    echo $e->getMessage();
}
