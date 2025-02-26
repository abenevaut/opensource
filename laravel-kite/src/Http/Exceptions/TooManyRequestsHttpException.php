<?php

namespace abenevaut\Kite\Http\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException as ParentHttpException;

final class TooManyRequestsHttpException extends ParentHttpException
{
    public function __construct(
        private Request $request,
        array $headers,
        string $message = 'Too Many Requests',
        \Throwable $previous = null
    ) {
        parent::__construct($headers['Retry-After'], $message, $previous, 429, $headers);
    }

    public function report()
    {
        Log::channel('info')->warning($this->getMessage(), $this->context());
    }

    public function render()
    {
        $seconds = $this->getHeaders()['Retry-After'];

        return Inertia::render('Errors/429', [
            'seconds' => $seconds,
            'message' => trans('exception.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ])
            ->toResponse($this->request)
            ->setStatusCode($this->getStatusCode())
            ->withHeaders($this->getHeaders());
    }

    public function context()
    {
        return [
            'sessions.id' => $this->request->session()->getId(),
        ];
    }
}
