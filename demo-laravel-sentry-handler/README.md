# Installation

```
cp .env.example .env
composer install
php artisan key:generate
```

Then edit `SENTRY_LARAVEL_DSN` variable in `.env`.

# Use the demo

On mono-repository root directory, run:

```
docker-compose up -d demo-laravel-sentry-handler
docker-compose exec demo-laravel-sentry-handler composer install
```

Then visit
- http://localhost:8091/report-std-exception
- http://localhost:8091/throw-std-exception-to-handler
- http://localhost:8091/report-scoped-exception
- http://localhost:8091/throw-scoped-exception-to-handler

Check your Sentry ;)
