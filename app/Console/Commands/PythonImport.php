<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use App\Models\Asset;

class PythonImport extends AbstractCommand
{

    protected $signature = 'python:import';

    protected $description = 'Import CSV for image metadata';

    public function handle()
    {
        if (Storage::disk('python')->exists('python-input.csv')) {
            $this->warn('python-input.csv is still being processed');
            return;
        }

        if (!Storage::disk('python')->exists('python-output.csv')) {
            $this->warn('python-output.csv not found');
            return;
        }

        $path = Storage::disk('python')->path('python-output.csv');

        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv->getRecords() as $row) {
            $image = Asset::images()->find($row['id']);

            if (!$image) {
                $this->info("{$row['id']} - not found");
                continue;
            }

            // https://github.com/JohannesBuchner/imagehash
            if (!empty($row['ahash'])) {
                $image->ahash = $row['ahash'];
            }

            if (!empty($row['dhash'])) {
                $image->dhash = $row['dhash'];
            }

            if (!empty($row['phash'])) {
                $image->phash = $row['phash'];
            }

            if (!empty($row['whash'])) {
                $image->whash = $row['whash'];
            }

            if (!empty($row['colorfulness'])) {
                $image->colorfulness = $row['colorfulness'];
            }

            $image->save();

            // Output for reference
            $this->info("{$image->id} - updated");
        }

        Storage::disk('python')->delete('python-output.csv');
    }

}
