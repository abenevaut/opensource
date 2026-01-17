# laravel-google-maps

## Installation

You can install the package via composer:

```bash
composer require abenevaut/laravel-google-maps
```

Add GoogleMaps service to your `config/services.php` file:

```php
'googlemaps' => [
    'baseUrl' => env('GOOGLE_MAPS_URL', 'https://places.googleapis.com'),
    'api_key' => env('GOOGLE_MAPS_API_KEY'),
    'debug' => env('GOOGLE_MAPS_DEBUG', false),
],
```

## Usage

```php
use abenevaut\GoogleMaps\Facades\GoogleMaps;

// Example: Search for nearby places
$results = GoogleMaps::placesNearby([
    'location' => '48.8588443,2.2943506', // Eiffel Tower
    'radius' => 1500,
    'type' => 'restaurant',
]);

// ... to be continued
```
