<?php

return [
    'api_url' => env('SOURCE_API_URL'),

    'username' => env('SOURCE_USERNAME'),
    'password' => env('SOURCE_PASSWORD'),

    'iiif_url' => env('IIIF_URL', 'https://www.artic.edu/iiif/2'),

    'uuid_prefix' => env('UUID_PREFIX'),

    'python_chunk_size' => is_numeric(env('PYTHON_CHUNK_SIZE')) ? (int) env('PYTHON_CHUNK_SIZE') : null,
];
