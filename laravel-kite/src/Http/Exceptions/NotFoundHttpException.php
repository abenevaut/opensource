<?php

namespace abenevaut\Kite\Http\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException as ParentHttpException;

final class NotFoundHttpException extends ParentHttpException
{
    public function __construct(
        private Request $request,
        array $headers,
        string $message = 'Not Found',
        \Throwable $previous = null
    ) {
        parent::__construct($message, $previous, 404, $headers);
    }

    public function report()
    {
        Log::channel('info')->info($this->getMessage(), $this->context());
    }

    public function render()
    {
        return Inertia::render('Errors/404', [
            'message' => trans('exception.not_found'),
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
