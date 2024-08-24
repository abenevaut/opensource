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
        $userID = '';
        if (Auth::guard('api')->check()) {
            // @phpstan-ignore-next-line
            $clientId = Auth::guard('api')->client()->id;
            // @phpstan-ignore-next-line
            $userID = $request->user()->getAuthIdentifier();
        }

        return [
            'client-id' => $clientId,
            'user-id' => $userID,
        ];
    }
}
