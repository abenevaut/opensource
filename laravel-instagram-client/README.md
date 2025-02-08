# laravel-instagram-client

## Installation

You can install the package via composer:

```bash
composer require abenevaut/laravel-instagram-client
```

Add instagram service to your `config/services.php` file:

```php
'instagram' => [
    'baseUrl' => 'https://i.instagram.com/api/v1', // Instagram API URL
    'debug' => env('INSTAGRAM_DEBUG', false), // Debug mode
],
```

## Usage

```php
use Abenevaut\InstagramClient\Facades\Instagram;

Instagram::getClient(): InstagramClient; // Get the Instagram client
Instagram::countFollowers(string $invitationLink): int; // Get the number of followers of an instagram account, like laravel.france
```
