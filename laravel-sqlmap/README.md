config/app.php

```php
<?php

return [

    // ...

    'csrf' => [
        'disable_validation' => env('APP_DISABLE_CSRF_VALIDATION_FOR_SQLMAP', false)
    ],
    
    'rate_limiter' => [
        'bypassed_user_agents' => [
            'sqlmap/1.8.4.7#dev (http://sqlmap.org)',
        ],
    ],
];
```

bootstrap/app.php, `withMiddleware` section

```php
<?php

$middleware
    ->replaceInGroup(
        'web',
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        \App\Http\Middleware\ValidateCsrfToken::class,
    )
```
