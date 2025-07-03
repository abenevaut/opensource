<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SQLMap Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for Laravel SQLMap package
    |
    */

    'enabled' => env('SQLMAP_ENABLED', false),

    'output_path' => env('SQLMAP_OUTPUT_PATH', storage_path('sqlmap')),

    'base_url' => env('SQLMAP_BASE_URL', env('APP_URL', 'http://127.0.0.1:8000')),

    'disable_csrf' => env('SQLMAP_DISABLE_CSRF', false),

    'bypassed_user_agents' => [
        'sqlmap/1.8.4.7#dev (http://sqlmap.org)',
        'sqlmap/*',
    ],

    'skip_routes' => [
        'api/documentation',
        'telescope',
        'horizon',
        'debugbar',
        '_ignition',
        'livewire',
        'admin/logs',
        'health-check',
    ],

    'test_parameters' => [
        'id' => '1',
        'user_id' => '1',
        'email' => 'test@example.com',
        'password' => 'password123',
        'name' => 'Test User',
        'username' => 'testuser',
        'search' => 'test',
        'filter' => 'active',
        'sort' => 'created_at',
        'page' => '1',
        'limit' => '10',
        'category_id' => '1',
        'status' => 'active',
        'token' => 'test_token',
        'api_key' => 'test_api_key',
    ],

    'sqlmap_options' => [
        'level' => 3,
        'risk' => 2,
        'batch' => true,
        'flush_session' => true,
        'fresh_queries' => true,
    ],
];
