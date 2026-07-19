<?php

return [
    /*
    |--------------------------------------------------------------------------
    | View Paths
    |--------------------------------------------------------------------------
    |
    | All Blade view files are stored in this directory. Add more paths if
    | you want to keep views in another location as well.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled Views
    |--------------------------------------------------------------------------
    |
    | This option determines where compiled Blade templates are stored.
    |
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

    /*
    |--------------------------------------------------------------------------
    | View Cache
    |--------------------------------------------------------------------------
    |
    | Enable or disable Blade view caching.
    |
    */

    'cache' => env('VIEW_CACHE', true),

    /*
    |--------------------------------------------------------------------------
    | Compiled Extension
    |--------------------------------------------------------------------------
    |
    | The file extension used for compiled views.
    |
    */

    'compiled_extension' => env('VIEW_COMPILED_EXTENSION', 'php'),

    /*
    |--------------------------------------------------------------------------
    | Check Cache Timestamps
    |--------------------------------------------------------------------------
    |
    | When enabled, Blade will check whether the compiled view is stale.
    |
    */

    'check_cache_timestamps' => env('VIEW_CHECK_CACHE_TIMESTAMPS', true),

    /*
    |--------------------------------------------------------------------------
    | Relative Hash
    |--------------------------------------------------------------------------
    |
    | Keep compiled view names relative to the application base path.
    |
    */

    'relative_hash' => env('VIEW_RELATIVE_HASH', false),
];
