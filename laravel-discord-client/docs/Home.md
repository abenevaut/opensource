# laravel-discord-client

## Installation

You can install the package via composer:

```bash
composer require abenevaut/laravel-discord-client
```

Add discord service to your `config/services.php` file:

```php
'discord' => [
    'baseUrl' => 'https://discord.com/api', // Discord API URL
    'debug' => env('BLUESKY_DEBUG', false), // Debug mode
],
```

## Usage

```php
use Abenevaut\DiscordClient\Facades\Discord;

Discord::getClient(): DiscordClient; // Get the Discord client
Discord::countFollowers(string $invitationLink): int; // Get the number of followers of a discord server, invitation link looks like "https://discord.gg/pwwPEXcFfU" (prefer the use link without expiration)
```
