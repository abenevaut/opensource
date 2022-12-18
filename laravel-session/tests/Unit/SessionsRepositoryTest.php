<?php

namespace Tests\Unit;

use abenevaut\Session\Domain\Users\Sessions\Repositories\SessionsRepository;
use Tests\TestCase;

class SessionsRepositoryTest extends TestCase
{
    public function testSessionsRepository()
    {
        $repository = new SessionsRepository();

        $this->assertEquals(0, $repository->onlineRegisteredUsers());
    }
}
