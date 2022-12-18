<?php

namespace abenevaut\Session\Domain\Users\Sessions\Builders;

use Illuminate\Database\Eloquent\Builder;

class SessionsBuilder extends Builder
{
    public function guests()
    {
        return $this
            ->query
            ->whereNull('user_id')
        ;
    }

    public function registered()
    {
        return $this
            ->query
            ->whereNotNull('user_id')
        ;
    }
}
