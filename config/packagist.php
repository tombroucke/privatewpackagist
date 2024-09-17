<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Package Vendor Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of the vendor that owns the packages. This value
    | is used when generating the Composer package name.
    |
    */

    'vendor' => env('PACKAGIST_VENDOR_NAME', 'privatewpackagist'),

    /*
    |--------------------------------------------------------------------------
    | WordPress User Agent
    |--------------------------------------------------------------------------
    |
    | This value is the user agent used when making requests to plugin update
    | endpoints. For compatibility, this defaults to the WordPress user agent.
    |
    */

    'user_agent' => env('PACKAGIST_USER_AGENT', 'WordPress/6.6.2'),

    /*
    |--------------------------------------------------------------------------
    | Update Recipes
    |--------------------------------------------------------------------------
    |
    | This value is the default path and namespace for update recipes. Update
    | recipes are classes that handle updating a plugin or package from an
    | external source.
    |
    */

    'recipes' => [
        'path' => app_path('Recipes'),
        'namespace' => 'App\\Recipes',
    ],

];
