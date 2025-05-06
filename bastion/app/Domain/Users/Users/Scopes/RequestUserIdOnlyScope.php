<?php

namespace App\Domain\Users\Users\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class RequestUserIdOnlyScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('users.id', request()->user()->id);
    }
}
