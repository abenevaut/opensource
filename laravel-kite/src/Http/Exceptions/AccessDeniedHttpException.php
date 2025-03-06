<?php

namespace abenevaut\Kite\Http\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException as ParentHttpException;

final class AccessDeniedHttpException extends ParentHttpException
{
    public function __construct(
        private Request $request,
        array $headers,
        string $message = 'Forbidden',
        \Throwable $previous = null
    ) {
        parent::__construct($message, $previous, 403, $headers);
    }

    public function report()
    {
        Log::channel('info')->warning($this->getMessage(), $this->context());
    }

    public function render()
    {
        return Inertia::render('Errors/403', [
            'message' => trans('exception.forbidden'),
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
