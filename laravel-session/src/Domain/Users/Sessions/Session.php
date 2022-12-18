<?php

namespace abenevaut\Session\Domain\Users\Sessions;

use abenevaut\Session\Domain\Users\Sessions\Builders\SessionsBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Session extends Model
{
    protected $table = 'sessions';

    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        $this->connection = Config::get('session.connection');

        parent::__construct($attributes);
    }

    public static function query(): SessionsBuilder
    {
        return parent::query();
    }

    public function newEloquentBuilder($query): SessionsBuilder
    {
        return new SessionsBuilder($query);
    }
}
