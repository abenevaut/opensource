<?php

namespace abenevaut\Infrastructure\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IdentifyClientRequestMiddleware extends ShareLogsContextMiddlewareAbstract
{
    /**
     * @return array<string, string>
     */
    protected function additionalContext(Request $request): array
    {
        $clientId = '';
        if (Auth::guard('api')->check()) {
            // @phpstan-ignore-next-line
            $clientId = Auth::guard('api')->client()->id;
        }

        return [
            'client-id' => $clientId,
        ];
    }
}
