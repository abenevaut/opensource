# laravel-linkedin-client

## Installation

You can install the package via composer:

```bash
composer require abenevaut/laravel-linkedin-client
```

Add Linkedin service to your `config/services.php` file:

```php
'linkedin' => [
    'baseUrl' => env('LINKEDIN_URL', 'https://api.linkedin.com/v2'),
    'api_key' => env('LINKEDIN_API_KEY'),
    'debug' => env('LINKEDIN_DEBUG', false),
],
```

## Usage

```php
use Abenevaut\Linkedin\Facades\Linkedin;

Linkedin::getClient(): LinkedinClient; // Get the Linkedin client
Linkedin::countGroupMembers(string $groupId): int; // Get the number of members of a group 
Linkedin::countCompanyFollowers(string $companyId): int; // Get the number of followers of a company, like 103381678 (abenevaut.dev)
```
