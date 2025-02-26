<?php

namespace abenevaut\Kite\Domain\Users\Users;

use abenevaut\Kite\Domain\Users\Users\Builders\UsersBuilder;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use Notifiable;
    use HasUlids;

    protected $table = 'users';

    protected $model = self::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public static function query(): UsersBuilder
    {
        return parent::query();
    }

    public function newEloquentBuilder($query): UsersBuilder
    {
        return new UsersBuilder($query);
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
