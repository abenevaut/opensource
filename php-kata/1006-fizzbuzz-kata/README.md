# KATA: Test your FizzBuzz

- [Exercise Summary](https://codingdojo.org/kata/FizzBuzz/)

## Setup your computer

- [Download PHP](https://windows.php.net/downloads/releases/php-8.2.11-nts-Win32-vs16-x64.zip)
- [Download Composer](https://getcomposer.org/download/)

## Initialize the project

```
git clone
cd kata-phpunit
composer install
php src/main.php
```

# Do the KATA

Following [this exercise summary](https://codingdojo.org/kata/FizzBuzz/):
## 0. clone the repository

### Solution

- Download [PHP 8.2 - VS16 x64 Non Thread Safe - Zip 30.24MB](https://windows.php.net/download/)
- Unzip
- Rename php.ini-development to php.ini
- Inside php.ini, uncomment `extension_dir = "ext"`
- Inside php.ini, uncomment `extension = openssl`
- Cut the unzipped directory to `C:\` root
- Edit Windows environment variable "Path" and add `C:\php-8.2.11-nts-Win32-vs16-x64`; now you are able to use `php` inside PowerShell
- Now follow the [composer installation guide](https://getcomposer.org/download/)
- Now you are able to `composer install` in this project directory

## 1. setup [PHPUnit](https://phpunit.de/)

### Solution

- Follow the [PHPUnit installation guide](https://docs.phpunit.de/en/10.4/installation.html#installing-phpunit); we recommend to install with composer

```powershell
composer require --dev phpunit/phpunit
```

- Now set up the basics settings for PHPUnit

```powershell
vendor/bin/phpunit --generate-configuration
New-Item -Path . -Name "tests" -ItemType "directory" 
```

- Now you are able to run PHPUnit, it will run without running any tests (there is no tests... so it's fine)

```powershell
vendor/bin/phpunit
```

- When executing, PHPUnit create a cache file we need to ignore in repository

```powershell
Add-Content -Path .\.gitignore -Value ".phpunit.cache"
```

## 2. code & test the FizzBuzz inside the FizzBuzz class (`src/FizzBuzz.php`)

### Solution

- complete `FizzBuzz` class and elaborate it with multiple methods that make small and very comprehensive task
- create your first test, the `FizzBuzzTest` class and complete the class with methods that test `FizzBuzz` class methods
  - the `FizzBuzzTest`, have to inherit from `PHPUnit\Framework\TestCase` to be able to use assertion methods and do test/validation ([have a look to the doc](https://docs.phpunit.de/en/10.0/assertions.html))
  - in `FizzBuzzTest`, each methods have to start with prefix `test` to be executed
- execute `vendor/bin/phpunit` to run test suite

## 3. create your first mock

execute fizzbuzz inside `transformFromDatabase()` on an integers list provided by Database::getNumbers().
but.. you cannot change `Database::getNumbers()`, so mock it!

### Add followings files:

- `src/DatabaseInterface.php`
```
<?php

namespace App;

interface DatabaseInterface
{
    /**
     * return a list of numbers
     *
     * @return array
     */
    public function getNumbers(): array;
}
```

- `src/Database.php`
```
<?php

namespace App;

class Database implements DatabaseInterface
{
    public function getNumbers(): array
    {
        throw new \Exception("DO NOT IMPLEMENT", 501);
    }
}
```

### Edit followings files:

- `src/FizzBuzz.php`
```
class FizzBuzz
{
    public function __construct(protected Database $database) {}

    public function transformFromDatabase(): array
    {
        $numbers = $this->database->getNumbers();

        throw new \Exception('Execute the FizzBuzz on your array of numbers');
    }
```

- `src/main.php`
```
<?php
require './vendor/autoload.php';

use App\Database;
use App\FizzBuzz;

$fizzBuzz = new FizzBuzz();
$fizzBuzz = new FizzBuzz(new Database());

try {
    $lines = $fizzBuzz->countTo100();
    foreach ($lines as $line) {
        echo $line . PHP_EOL;
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

try {
    $lines = $fizzBuzz->transformFromDatabase();
} catch (Exception $e) {
    if (501 === $e->getCode()) {
        echo 'Database::getNumbers() not implemented';
    }
}
```

### Solution

- first, we will check that the exception from `Database::getNumbers()` is thrown, follow `tests/FizzBuzzTest::test_catch_getNumbers_exception()`
  - notice that expectations are placed before the unit tested method
- to get around this exception and test `FizzBuzz::transformFromDatabase()` method, we have to mock the `Database::getNumbers()` method
  - https://docs.phpunit.de/en/10.4/test-doubles.html#mock-objects

## 4. bonus: do a mock of a database interface that should return a list of Numbers, based on `DatabaseInterface`

### Solution

- we can copy the previous mock test to `tests/FizzBuzzTest::test_mock_getNumbers_from_interface`
- use `createConfiguredStub` rather than `createConfiguredMock`
- now, test does not pass because of `TypeError: App\FizzBuzz::__construct(): Argument #1 ($database) must be of type App\Database`
  - you have to change the type in `App\FizzBuzz` constructor `public function __construct(protected DatabaseInterface $database) {}`

## 5. bonus: install & use [infection](https://infection.github.io/guide/)

### Solution

- run `composer require infection/infection`
- https://xdebug.org/download and download "PHP 8.2 VS16 (64 bit)"
  - rename downloaded file to `php_xdebug.dll`
  - paste in in `C:\php-8.2.11-nts-Win32-vs16-x64\ext`
  - add in `C:\php-8.2.11-nts-Win32-vs16-x64\php.ini` => `extension=xdebug` to load the extension
- run `vendor/bin/infection`
  - on first run, the tool required some setup, then execute tests

You are stuck in a step ? Checkout `stepX` branch to get help
