<?php

require './vendor/autoload.php';

use App\Database;
use App\FizzBuzz;

$fizzBuzz = new FizzBuzz(new Database());

try {
    $lines = $fizzBuzz->countTo100();

    foreach ($lines as $line) {
        echo $line . PHP_EOL;
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

try {
    $lines = $fizzBuzz->transformFromDatabase();
} catch (Exception $e) {
    if (501 === $e->getCode()) {
        echo 'Database::getNumbers() not implemented';
    }
}
