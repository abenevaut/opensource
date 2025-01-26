# laravel-bluesky-client

## Installation

You can install the package via composer:

```bash
composer require abenevaut/laravel-bluesky-client
```

Add bluesky service to your `config/services.php` file:

```php
'bluesky' => [
    'baseUrl' => env('BLUESKY_URL', 'https://bsky.social'), // Bluesky API URL, default is the official Bluesky URL
    'identifier' => env('BLUESKY_IDENTIFIER'), // Your Bluesky identifier, like abenevaut.bsky.social
    'password' => env('BLUESKY_PASSWORD'), // Your Bluesky password
    'debug' => env('BLUESKY_DEBUG', false), // Debug mode
],
```

## Usage

```php
use Abenevaut\BlueskyClient\Facades\BlueSky;

BlueSky::getClient(): BlueSkyClient; // Get the BlueSky client
BlueSky::countFollowers(string $account): int; // Get the number of followers of an account, like abenevaut.bsky.social
```
