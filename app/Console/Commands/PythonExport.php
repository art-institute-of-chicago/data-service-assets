<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use App\Models\Asset;

class PythonExport extends AbstractCommand
{

    protected $signature = 'python:export';

    protected $description = 'Export CSV of image ids for Python processing';

    public function handle()
    {
        if (Storage::disk('python')->exists('python-input.csv')) {
            $this->warn('python-input.csv already exists');
            return;
        }

        if (Storage::disk('python')->exists('python-output.csv')) {
            $this->warn('python-output.csv has not been processed yet');
            return;
        }

        Storage::disk('python')->put('export.lock', '');

        $path = Storage::disk('python')->path('python-input.csv');
        $csv = Writer::createFromPath($path, 'w');

        $csv->insertOne([
            'id',
            'path',
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
        $images = $images->where(function ($query) {
            $query->whereNull('ahash')
                ->orWhereNull('dhash')
                ->orWhereNull('phash')
                ->orWhereNull('whash')
                ->orWhereNull('colorfulness');
        });

        $this->warn($images->count() . ' images will be exported');

        if (config('source.python_chunk_size')) {
            $images = $images->limit(config('source.python_chunk_size'));
        }

        foreach ($images->cursor() as $image) {
            $row = [
                'id' => $image->id,
                'path' => Asset::getImagePath($image->id),
                'ahash' => isset($image->ahash) ? null : true,
                'dhash' => isset($image->dhash) ? null : true,
                'phash' => isset($image->phash) ? null : true,
                'whash' => isset($image->whash) ? null : true,
                'colorfulness' => isset($image->colorfulness) ? null : true,
            ];

            $csv->insertOne($row);

            $this->info(json_encode($row));
        }

        Storage::disk('python')->delete('export.lock');
    }

}
