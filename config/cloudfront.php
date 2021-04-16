<?php

return [
    'key' => env('AWS_KEY'),
    'secret' => env('AWS_SECRET'),

    'region' => env('AWS_REGION', 'us-east-1'),
    'sdk_version' => env('AWS_SDK_VERSION', '2016-01-13'),

    'distribution' => env('AWS_CLOUDFRONT_DISTRIBUTION'),

    'http_proxy' => env('AWS_HTTP_PROXY'),
];
