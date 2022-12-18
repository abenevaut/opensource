## laravel-session

This package help to manage basics of Laravel session.

Inspired from [this article from hevodata.com](https://hevodata.com/learn/google-metrics/#:~:text=A%20Google%20Dimension%20in%20Google,sort%20data%20as%20per%20requirements.), laravel-session try to manage Hits, Sessions & Users in the way of Google Analytics does.

> 1) A Hit refers to any user interaction taking place on the website resulting in data being sent to Google Analytics. Each time a user interaction triggers the tracking code, Analytics records that activity and sends it to Google as a Hit.
> 2) The Session scope is a more time-based scope and considered to be at a level higher than the Hit-level scope. A Session consists of all Hits that occur in one Session for a given user. Google Metrics and Dimensions on Session-level collect all relevant data about a session. Examples of Google Dimensions include Source/Medium, Landing Page, Device Category, etc., and Google Metrics include Sessions, Bounce Rate, Exit Rate, etc.
> 3) The User scope is the highest level at which the user data is organized in Google Analytics. One user can have multiple Sessions, and one Session can have more Hits. Examples of Google Dimensions in the User scope include User Type, Days since the last Session, etc., and Google Metrics include Users, New Sessions, etc.

- [Read more at hevodata.com](https://hevodata.com/learn/google-metrics/#:~:text=A%20Google%20Dimension%20in%20Google,sort%20data%20as%20per%20requirements.)

## Before to start

You have to follow the basic [Laravel session configuration](https://laravel.com/docs/9.x/session#configuration).

## Install

```shell
composer require abenevaut/laravel-session
```

```php
<?php

/*
 * app/Http/Kernel.php
 */

return [
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \abenevaut\Session\Http\Middleware\IdentifyRequestMiddleware::class,
// ...
            \abenevaut\Session\Http\Middleware\AfterMiddleware::class,
        ],
```

### IdentifyRequestMiddleware

- Should be right after `\Illuminate\Session\Middleware\StartSession`
- Add three info to logs (as "shared context" between loggers) : `request-hit-id` (always), `session-id` (always) & `user-id` (if exists)
- Add one request header : `request-hit-id` (always)

### AfterMiddleware

- Should be triggered as last middleware
- Dispatch `TimeSpentOnAppByUserEvent`
- **Warning** : This event **works with `database` session driver only**, you have to extend the middleware and the event if you want to work with another driver 

```php
<?php

/*
 * app/Providers/EventServiceProvider.php
 */

return [
    protected $listen = [
        
        \abenevaut\Session\App\Events\TimeSpentOnAppByUserEvent::class => [
            \App\Listeners\RecordTimeSpentOnAppByUserListener::class,
        ],

    ];
```

### RecordTimeSpentOnAppByUserListener

- This listener log (in log file) time spent on App by a user
