<?php

namespace abenevaut\Infrastructure\Http\Middleware;

use Illuminate\Http\Request;

class IdentifyUserRequestMiddleware
{
    protected function additionalContext(Request $request): array
    {
        return [
            'user-id' => auth()?->user()?->id,
        ];
    }
}
