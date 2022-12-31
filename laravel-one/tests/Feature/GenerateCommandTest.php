<?php

/*
 * Remove dist directory before each test
 */

it('generate laravel-one web pages', function () {
    $this
        ->artisan('generate', ['url' => 'https://laravel-one.test'])
        ->expectsOutput('2 pages to generate')
        ->assertExitCode(0);
});
