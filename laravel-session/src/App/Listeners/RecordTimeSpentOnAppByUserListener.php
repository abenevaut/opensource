<?php

namespace abenevaut\Session\App\Listeners;

use abenevaut\Session\App\Events\TimeSpentOnAppByUserEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RecordTimeSpentOnAppByUserListener
{
    public function handle(TimeSpentOnAppByUserEvent $event): void
    {
        $context = $this->computeTimeSpentOnAppByUser($event);

        Log::info("User passed {$context['timePassedOnSiteSinceLastActivityInSeconds']} second(s) on App.", $context);
    }

    protected function computeTimeSpentOnAppByUser(TimeSpentOnAppByUserEvent $event): array
    {
        $timePassedOnSiteSinceLastActivityInSeconds = 0;
        $userLastActivityAt = $event->userLastActivityAt;
        $respondDate = Carbon::parse($event->lastResponseDate);

        if ($userLastActivityAt) {
            $userLastActivityAt = Carbon::createFromTimestamp($userLastActivityAt);
            $timePassedOnSiteSinceLastActivityInSeconds = $respondDate->timestamp - $userLastActivityAt->timestamp;
        }

        return [
            'currentRespondDate' => $respondDate->toString(),
            'method' => $event->requestMethod,
            'previousUrl' => $event->previousUrl,
            'timePassedOnSiteSinceLastActivityInSeconds' => $timePassedOnSiteSinceLastActivityInSeconds,
            'userLastActivityDate' => $userLastActivityAt ? $userLastActivityAt->toString() : 'session not initialized',
        ];
    }
}
