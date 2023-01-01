<?php

beforeEach(function () {
    $files = array_diff(scandir('./dist'), ['.', '..']);

    foreach ($files as $file) {
        unlink("./dist/$file");
    }

    return rmdir('./dist');
});

it('generate laravel-one web pages', function () {
    $this
        ->artisan('generate', ['url' => 'https://laravel-one.test'])
        ->expectsOutput('2 pages to generate')
        ->assertExitCode(0);

    expect('./dist')->toBeWritableDirectory();
    expect('./.cache')->toBeWritableDirectory();

    $files = array_diff(scandir('./dist'), ['.', '..']);

    expect(count($files))->toBe(3);
});
