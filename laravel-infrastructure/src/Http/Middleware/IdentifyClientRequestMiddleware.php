<?php

namespace abenevaut\Infrastructure\Http\Middleware;

use Illuminate\Http\Request;

class IdentifyClientRequestMiddleware extends ShareLogsContextMiddlewareAbstract
{
    protected function additionalContext(Request $request): array
    {
        return [
            'client-id' => auth('api')?->client()?->id
        ];
    }
}
