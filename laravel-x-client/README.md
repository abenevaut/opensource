# laravel-x-client

## Installation

You can install the package via composer:

```bash
composer require abenevaut/laravel-x-client
```

Add x service to your `config/services.php` file:

```php
'x' => [
    'baseUrl' => env('X_URL', 'https://api.x.com/2'), // x API URL
    'client_id' => env('X_CLIENT_ID'), // Your x client id
    'client_secret' => env('X_CLIENT_SECRET'), // Your x client secret
    'debug' => env('X_DEBUG', false), // Debug mode
],
```

## Usage

```php
use abenevaut\X\Facades\X;

X::getClient(): XClient; // Get the x client
X::countFollowers(string $account): int; // Get the number of followers of an account, like @abenevaut
```
