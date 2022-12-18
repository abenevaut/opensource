# Installation

```
cp .env.example .env
composer install
php artisan key:generate
```

Then edit `SENTRY_LARAVEL_DSN` variable in `.env`.

# Use the demo

```
php artisan serve
```

Then visit
- http://127.0.0.1:8000/report-std-exception
- http://127.0.0.1:8000/throw-std-exception-to-handler
- http://127.0.0.1:8000/report-scoped-exception
- http://127.0.0.1:8000/throw-scoped-exception-to-handler

Check you Sentry ;)
