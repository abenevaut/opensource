<?php

namespace abenevaut\SentryHandler\Traits;

use abenevaut\SentryHandler\Contracts\ExceptionAbstract;
use Sentry\Laravel\Facade as SentryFacade;
use Throwable;

/**
 * Should be used in `Illuminate\Foundation\Exceptions\Handler` context.
 */
trait SentryHandlerTrait
{
    /**
     * @return bool
     */
    public function isSentryBounded(): bool
    {
        return $this->container->bound('sentry') === true;
    }

    /**
     * Allows to report to Sentry all standard exceptions thrown.
     *
     * @param  Throwable  $exception
     */
    public function reportToSentry(Throwable $exception): void
    {
        if ($this->isSentryBounded() && $this->shouldReport($exception) === true) {
            SentryFacade::captureException($exception);
        }
    }
}
