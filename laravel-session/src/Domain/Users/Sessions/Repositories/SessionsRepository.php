<?php

namespace abenevaut\Session\Domain\Users\Sessions\Repositories;

use abenevaut\Session\Domain\Users\Sessions\Session;

class SessionsRepository
{
    public function onlineGuests()
    {
        return Session::query()
            ->guests()
            ->count()
        ;
    }

    public function onlineRegisteredUsers()
    {
        return Session::query()
            ->registered()
            ->count()
        ;
    }

    public function onlineUsers()
    {
        return $this->onlineRegisteredUsers()
            + $this->onlineGuests()
        ;
    }
}
