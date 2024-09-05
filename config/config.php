<?php

return [
    /*
 |--------------------------------------------------------------------------
 | Application URL
 |--------------------------------------------------------------------------
 |
 | This URL is used by the console to properly generate URLs when using
 | the Artisan command line tool. You should set this to the root of
 | your application so that it is used when running Artisan tasks.
 |
 */
    'url' => env('CENTRAL_SERVER_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application ID
    |--------------------------------------------------------------------------
    |
    | This is the unique identifier for your application in the central server.
    | It is used for authentication and authorization purposes.
    |
    */
    'app_id' => env('CENTRAL_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Application Secret
    |--------------------------------------------------------------------------
    |
    | This is the secret key for your application in the central server.
    | It is used for secure communication and should be kept confidential.
    |
    */
    'app_secret' => env('CENTRAL_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Permission Prefix
    |--------------------------------------------------------------------------
    |
    | This prefix is used to namespace permissions within the central server.
    | For example, if the prefix is 'collector', a permission might be
    | 'collector:view-users'.
    |
    */
    'permission_prefix' => 'collector',

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Define the Eloquent models used in the application. These models will
    | be referenced by the central server for various operations.
    |
    */
    'models' => [
        'user' => \Koeeru\Central\Models\User::class,
        'company' => \Koeeru\Central\Models\Company::class,
    ],

];
