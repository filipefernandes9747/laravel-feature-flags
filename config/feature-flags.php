<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Flags Definitions
    |--------------------------------------------------------------------------
    |
    | All the defined feature flags go here.
    |
    */

    'flags' => [],

    /*
    |--------------------------------------------------------------------------
    | UI Options
    |--------------------------------------------------------------------------
    */

    'ui' => [
        'enabled' => true,
        'middleware' => [],
        'route_prefix' => 'admin/flags',
    ],

    /*
    |--------------------------------------------------------------------------
    | Environments Options
    |--------------------------------------------------------------------------
    */

    'environments' => [
        'dev',
        'prod',
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Options
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'enabled' => true,
        'ttl' => 300, // seconds
    ],
];
