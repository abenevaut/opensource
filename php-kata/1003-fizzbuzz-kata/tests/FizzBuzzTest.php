<?php

namespace Tests;

use App\FizzBuzz;
use PHPUnit\Framework\TestCase;

class FizzBuzzTest extends TestCase
{
    public function test_do_modulo_three()
    {
        $fizzBuzz = new FizzBuzz();

        $this->assertSame('Fizz', $fizzBuzz->doModuloThree(3));
        $this->assertEmpty($fizzBuzz->doModuloThree(5));
        $this->assertEmpty($fizzBuzz->doModuloThree(2));
    }

    public function test_do_modulo_five()
    {
        $fizzBuzz = new FizzBuzz();

        $this->assertSame('Buzz', $fizzBuzz->doModuloFive(5));
        $this->assertEmpty($fizzBuzz->doModuloFive(3));
        $this->assertEmpty($fizzBuzz->doModuloFive(2));
    }

    public function test_do_fizz_buzz()
    {
        $fizzBuzz = new FizzBuzz();

        $this->assertSame('2', $fizzBuzz->doFizzBuzz(2));
        $this->assertSame('Fizz', $fizzBuzz->doFizzBuzz(3));
        $this->assertSame('Buzz', $fizzBuzz->doFizzBuzz(5));
        $this->assertSame('FizzBuzz', $fizzBuzz->doFizzBuzz(15));
    }

    public function test_count_to_hundred()
    {
        $fizzBuzz = new FizzBuzz();

        $this->assertCount(100, $fizzBuzz->countTo100());
    }
}
