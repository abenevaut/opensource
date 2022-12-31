<?php

use App\GeneratorSettings;
use function Pest\Faker\faker;

test('GeneratorSettings', function () {

    $url = faker()->url;
    $plugins = [];

    $generatorSettings = new GeneratorSettings($url, $plugins);

    expect($generatorSettings->url)->toBe($url);
    expect($generatorSettings->plugins)->toBe($plugins);
});
