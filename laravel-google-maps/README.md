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
use abenevaut\GoogleMaps\Facades\GoogleMaps;

// Example: Search for nearby places
$results = GoogleMaps::placesNearby([
    'location' => '48.8588443,2.2943506', // Eiffel Tower
    'radius' => 1500,
    'type' => 'restaurant',
]);

// ... to be continued
```
