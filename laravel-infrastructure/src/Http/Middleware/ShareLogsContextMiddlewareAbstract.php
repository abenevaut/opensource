<?php

namespace abenevaut\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class ShareLogsContextMiddlewareAbstract
{
    abstract protected function additionalContext(Request $request): array;

    public function handle(Request $request, Closure $next)
    {
        $sharedContext = array_merge(
            $this->requestHitId(),
            $this->additionalContext($request)
        );

        Log::shareContext($sharedContext);

        return $next($request)
            ->header('REQUEST-HIT-ID', $sharedContext['request-hit-id']);
    }

    private function requestHitId(): array
    {
        return [
            'request-hit-id' => (string) Str::ulid()
        ];
    }
}
