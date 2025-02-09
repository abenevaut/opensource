# laravel-twitch-client

## Installation

You can install the package via composer:

```bash
composer require abenevaut/laravel-bluesky-client
```

Add Twitch service to your `config/services.php` file:

```php
'twitch' => [
    'oauthBaseUrl' => env('TWITCH_OAUTH_URL', 'https://id.twitch.tv/oauth2/token'), // Twitch OAuth URL
    'baseUrl' => env('TWITCH_URL', 'https://api.twitch.tv/helix'), // Twitch API URL
    'client_id' => env('TWITCH_CLIENT_ID'), // Your Twitch client id
    'client_secret' => env('TWITCH_CLIENT_SECRET'), // Your Twitch client secret
    'debug' => env('TWITCH_DEBUG', false), // Debug mode
],
```

## Usage

```php
use Abenevaut\Twitch\Facades\Twitch;

Twitch::getClient(): TwitchClient; // Get the Twitch client
Twitch::countFollowers(string $broadcaster): int; // Get the number of followers of an account, like abenevaut
```
