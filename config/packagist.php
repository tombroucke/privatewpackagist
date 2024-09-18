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
    | Date Time Format
    |--------------------------------------------------------------------------
    |
    | This value is the default date time format used when displaying dates and
    | times in the application. This value is used when displaying dates and
    | times in the application.
    |
    */
    'date_time_format' => env('PACKAGIST_DATE_TIME_FORMAT', 'Y-m-d H:i:s'),

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
