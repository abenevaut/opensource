<?php

namespace abenevaut\Session\App\Events;

use abenevaut\Session\Domain\Users\Sessions\Session;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Illuminate\Queue\SerializesModels;

class TimeSpentOnAppByUserEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public readonly ?string $previousUrl;
    public readonly string $requestMethod;
    public readonly string $lastResponseDate;
    public readonly ?int $userLastActivityAt;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Request $request, SymfonyResponse $response)
    {
        $this->previousUrl = $request->session()->previousUrl() ?? null;
        $this->requestMethod = $request->getMethod();
        $this->lastResponseDate = $response->headers->get('date');
        // Should serialize in the event to do not risk to lose this data with garbage collector
        $this->userLastActivityAt = Session::find($request->session()->getId())?->first()?->last_activity;
    }
}
