<?php

return [


    /*
    |--------------------------------------------------------------------------
    | API URL
    |--------------------------------------------------------------------------
    |
    | This value determines the URL of where to get source information from.
    | Set this in your ".env" file.
    |
    */

    'api_url' => env('SOURCE_API_URL', 'http://exampleapi.source.com/'),

    'username' => env('SOURCE_USERNAME', 'username'),
    'password' => env('SOURCE_PASSWORD', '********'),

    'iiif_url' => env('IIIF_URL', 'https://www.artic.edu/iiif/2'),

    'uuid_prefix' => env('UUID_PREFIX', ''),
];
