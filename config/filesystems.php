<?php

return [

    'disks' => [
        'images' => [
            'driver' => 'local',
            'root' => env('ROOT_IMAGE_PATH') ?: storage_path('images'),
        ],
    ],

];
