<?php

namespace abenevaut\Session\App\Jobs;

use App\Domain\Users\Sessions\Session;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class RecordTimeSpentOnAppByUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private readonly string $sessionId;
    private readonly ?string $previousUrl;
    private readonly string $requestMethod;
    private readonly string $lastResponseDate;
    private readonly ?int $userLastActivityAt;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Request $request, Response $response)
    {
        $this->sessionId = $request->session()->getId();
        $this->previousUrl = $request->session()->previousUrl() ?? null;
        $this->requestMethod = $request->getMethod();
        $this->lastResponseDate = $response->headers->get('date');
        $this->userLastActivityAt = Session::find($this->sessionId)?->first()?->last_activity;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $timePassedOnSiteSinceLastActivityInSeconds = 0;
        $userLastActivityAt = $this->userLastActivityAt;
        $respondDate = Carbon::parse($this->lastResponseDate);

        if ($userLastActivityAt) {
            $userLastActivityAt = Carbon::createFromTimestamp($userLastActivityAt);
            $timePassedOnSiteSinceLastActivityInSeconds = $respondDate->timestamp - $userLastActivityAt->timestamp;
        }

        Log::channel('info')
            ->info("User passed {$timePassedOnSiteSinceLastActivityInSeconds} second(s) on App.", [
                'sessions.id' => $this->sessionId,
                'currentRespondDate' => $respondDate->toString(),
                'userLastActivityDate' => $userLastActivityAt
                    ? $userLastActivityAt->toString()
                    : 'session not initialized',
                'timePassedOnSiteSinceLastActivityInSeconds' => $timePassedOnSiteSinceLastActivityInSeconds,
                'method' => $this->requestMethod,
                'previousUrl' => $this->previousUrl,
            ]);
    }
}
