<?php

namespace App\Console\Commands;

use League\Csv\Writer;
use App\Asset;

class PythonExport extends AbstractCommand
{

    protected $signature = 'python:export';

    protected $description = 'Export CSV of image ids for Python processing';

    public function handle()
    {
        $path = storage_path() . '/app/python-input.csv';

        $csv = Writer::createFromPath($path, 'w');

        $csv->insertOne([
            'id',
            'ahash',
            'dhash',
            'phash',
            'whash',
            'colorfulness',
        ]);

        $images = Asset::images();

        if (config('app.env') === 'local') {
            $images = $images->whereNotNull('image_downloaded_at');
        }

        // Only target images that are missing fields provided by Python
        $images = $images->where(function($query) {
            $query->whereNull('ahash')
                ->orWhereNull('dhash')
                ->orWhereNull('phash')
                ->orWhereNull('whash')
                ->orWhereNull('colorfulness');
        });

        if (!$this->confirm($images->count() . ' images will be exported. Proceed?'))
        {
            return;
        }

        foreach ($images->cursor() as $image)
        {
            $row = [
                'id' => $image->id,
                'ahash' => isset($image->ahash) ? null : true,
                'dhash' => isset($image->dhash) ? null : true,
                'phash' => isset($image->phash) ? null : true,
                'whash' => isset($image->whash) ? null : true,
                'colorfulness' => isset($image->colorfulness) ? null : true,
            ];

            $csv->insertOne($row);

            $this->info(json_encode($row));
        }
    }

}
