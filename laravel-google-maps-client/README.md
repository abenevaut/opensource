# laravel-google-maps

## Installation

You can install the package via composer:

```bash
composer require abenevaut/laravel-google-maps
```

@TODO: explain how to install and use `config/google-maps.php`

## Usage

```php
use abenevaut\GoogleMaps\Facades\GoogleMaps;

// Example: Search for nearby places
$results = GoogleMaps::service('places')
    ->getClient()
    ->placesNearby([
        'location' => '48.8588443,2.2943506', // Eiffel Tower
        'radius' => 1500,
        'type' => 'restaurant',
    ]);
```
