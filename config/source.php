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

];
