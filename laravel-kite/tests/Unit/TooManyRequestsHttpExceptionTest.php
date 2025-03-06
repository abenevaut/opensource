<?php

namespace Unit;

use abenevaut\Kite\Http\Exceptions\TooManyRequestsHttpException;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class TooManyRequestsHttpExceptionTest extends TestCase
{
    public function test_default_params(): void
    {
        $seconds = 5;
        $headers = [
            'Retry-After' => $seconds
        ];
        $exception = new TooManyRequestsHttpException(
            new Request(),
            $headers
        );

        $this->assertSame(429, $exception->getCode());
        $this->assertSame('Too Many Requests', $exception->getMessage());
    }
}
