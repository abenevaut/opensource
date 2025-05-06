<?php

use ApiPlatform\Metadata\UrlGeneratorInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;

return [
    'title' => 'Authentication Bastion',
    'description' => 'abenevaut OAuth2 Bastion',
    'version' => '1.0.0',
    'show_webby' => false,

    'routes' => [
        'domain' => null,
        // Global middleware applied to every API Platform routes
        // 'middleware' => []
    ],

    'resources' => [
        app_path('Domain'),
    ],

    'formats' => [
        'json' => ['application/json'],
        'jsonld' => ['application/ld+json'],
        //'jsonapi' => ['application/vnd.api+json'],
        //'csv' => ['text/csv'],
    ],

    'patch_formats' => [
        'json' => ['application/merge-patch+json'],
    ],

    'docs_formats' => [
        'jsonld' => ['application/ld+json'],
        //'jsonapi' => ['application/vnd.api+json'],
        'jsonopenapi' => ['application/vnd.openapi+json'],
        'html' => ['text/html'],
    ],

    'error_formats' => [
        'jsonproblem' => ['application/problem+json'],
    ],

    'defaults' => [
        'pagination_enabled' => true,
        'pagination_partial' => false,
        'pagination_client_enabled' => false,
        'pagination_client_items_per_page' => false,
        'pagination_client_partial' => false,
        'pagination_items_per_page' => 30,
        'pagination_maximum_items_per_page' => 30,
        'route_prefix' => '/api',
        'middleware' => [
            'api',
        ],
    ],

    'pagination' => [
        'page_parameter_name' => 'page',
        'enabled_parameter_name' => 'pagination',
        'items_per_page_parameter_name' => 'itemsPerPage',
        'partial_parameter_name' => 'partial',
    ],

    'graphql' => [
        'enabled' => false,
        'nesting_separator' => '__',
        'introspection' => ['enabled' => true],
        'max_query_complexity' => 500,
        'max_query_depth' => 200
        // 'middleware' => null
    ],

    'graphiql' => [
        // 'enabled' => true,
        // 'domain' => null,
        // 'middleware' => null
    ],

    'exception_to_status' => [
        AuthenticationException::class => 401,
        AuthorizationException::class => 403
    ],

    'swagger_ui' => [
        'enabled' => env('APP_ENV', 'production') === 'local',
        'oauth' => [
            'enabled' => false,
            'type' => 'oauth2',
            'flow' => 'authorizationCode',
            'tokenUrl' => env('APP_ENV') . '/oauth/token',
            'authorizationUrl' => env('APP_ENV') . '/oauth/authorize',
            'refreshUrl' => env('APP_ENV') . '/oauth/refresh',
            'scopes' => [
//                'scope1' => 'Description scope 1'
            ],
            'pkce' => false
        ],
    ],

    // 'openapi' => [
    //     'tags' => []
    // ],

    'url_generation_strategy' => UrlGeneratorInterface::ABS_PATH,

    'serializer' => [
        'hydra_prefix' => false,
        // 'datetime_format' => \DateTimeInterface::RFC3339
    ],

    // we recommend using "file" or "acpu"
    'cache' => 'file'
];
