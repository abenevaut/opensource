# laravel-sentry-handler

Package that facilitates sentry integration with context scoped exceptions that are able to transport data when an exception happened.

## Install
```shell
composer require abenevaut/laravel-sentry-handler
php artisan sentry:publish --dsn=
```

## Usage

### Update ExceptionHandler

Scoped Exception vendorized in this package are able to report themself to Sentry.
Because we probably want to report all exceptions to Sentry, we are able to implement `$this->reportSentry($e);` to record them to Sentry.

#### Inherited Handler

In `app/Exceptions/Handler.php`, replace `use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;` by `use abenevaut\SentryHandler\Handler as ExceptionHandler;`

If you already customized your exception handler, be sure to adjust your `report()` method:
```php
public function report(\Throwable $e): void
{
    // Report standard exceptions to sentry
    $this->reportSentry($e);

    parent::report($e);
}
```

Note: that method is used in [demo](https://github.com/abenevaut/demo-laravel-sentry-handler)

#### Handler Trait

In `app/Exceptions/Handler.php`, add `use SentryHandlerTrait;` in `App\Exceptions\Handler` class.

Then adjust your `report()` method:
```php
public function report(\Throwable $e): void
{
    // Report standard exceptions to sentry
    $this->reportSentry($e);

    parent::report($e);
}
```

#### Test Sentry with standard exceptions

```
php artisan sentry:test
```

### Scoped Exceptions

Laravel ExceptionHandler allows an exception to report herself by implementing `report()` method.
We use that place to compute exception context and then throw it to Sentry.




## Tests
```shell
vendor/bin/smelly-code-detector inspect src
vendor/bin/phpunit
```
