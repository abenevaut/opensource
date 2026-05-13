<?php

namespace Tests;

use App\Database;
use App\FizzBuzz;
use PHPUnit\Framework\TestCase;

class FizzBuzzTest extends TestCase
{
    public function test_do_modulo_three()
    {
        $fizzBuzz = new FizzBuzz(new Database());

        $this->assertSame('Fizz', $fizzBuzz->doModuloThree(3));
        $this->assertEmpty($fizzBuzz->doModuloThree(5));
        $this->assertEmpty($fizzBuzz->doModuloThree(2));
    }

    public function test_do_modulo_five()
    {
        $fizzBuzz = new FizzBuzz(new Database());

        $this->assertSame('Buzz', $fizzBuzz->doModuloFive(5));
        $this->assertEmpty($fizzBuzz->doModuloFive(3));
        $this->assertEmpty($fizzBuzz->doModuloFive(2));
    }

    public function test_do_fizz_buzz()
    {
        $fizzBuzz = new FizzBuzz(new Database());

        $this->assertSame('2', $fizzBuzz->doFizzBuzz(2));
        $this->assertSame('Fizz', $fizzBuzz->doFizzBuzz(3));
        $this->assertSame('Buzz', $fizzBuzz->doFizzBuzz(5));
        $this->assertSame('FizzBuzz', $fizzBuzz->doFizzBuzz(15));
    }

    public function test_count_to_hundred()
    {
        $fizzBuzz = new FizzBuzz(new Database());

        $this->assertCount(100, $fizzBuzz->countTo100());
    }

    public function test_catch_getNumbers_exception()
    {
        $fizzBuzz = new FizzBuzz(new Database());

        /*
         * We expect some future behaviour
         */
        $this->expectExceptionCode(501);
        $this->expectExceptionMessage('DO NOT IMPLEMENT');

        $fizzBuzz->transformFromDatabase();
    }

    public function test_mock_getNumbers()
    {
        $doFizzBuzzFor = [1, 15, 3, 5];
        $expectedFizzBuzzResults = [1 => '1', 15 => 'FizzBuzz', 3 => 'Fizz', 5 => 'Buzz'];

        $mockDatabase = $this->createConfiguredMock(Database::class, [
            'getNumbers' => $doFizzBuzzFor,
        ]);

        $fizzBuzz = new FizzBuzz($mockDatabase);

        $results = $fizzBuzz->transformFromDatabase();

        $this->assertSame($expectedFizzBuzzResults, $results);
    }
}
