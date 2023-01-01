<?php

beforeEach(function () {
    $distDirectoryExists = is_dir('./dist');

    if ($distDirectoryExists) {
        $files = array_diff(scandir('./dist'), ['.', '..']);

        foreach ($files as $file) {
            unlink("./dist/$file");
        }

        rmdir('./dist');
    }
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
