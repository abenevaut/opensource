# laravel-google-maps

## Installation

You can install the package via composer:

```bash
composer require abenevaut/laravel-google-maps
```

Add GoogleMaps service to your `config/services.php` file:

```php
'googlemaps' => [
    'baseUrl' => env('GOOGLEMAPS_URL', 'https://places.googleapis.com/v1/places:searchNearby'),
    'api_key' => env('GOOGLEMAPS_API_KEY'),
    'debug' => env('GOOGLEMAPS_DEBUG', false),
],
```

## Usage

```php
use Abenevaut\GoogleMaps\Facades\GoogleMaps;

GoogleMaps::getClient(): GoogleMaps; // Get the Google Maps client

// ... to be continued
```
