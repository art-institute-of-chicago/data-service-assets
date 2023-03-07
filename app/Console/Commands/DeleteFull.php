<?php

namespace App\Console\Commands;

use Illuminate\Support\Arr;
use App\Models\Asset;

class DeleteFull extends AbstractCommand
{
    protected $signature = 'delete:full';

    protected $description = 'Compare our database against NetX to detect deleted assets';

    public function handle()
    {
        foreach (Asset::$types as $type) {
            $this->info('Checking ' . $type . 's');

            $bar = $this->output->createProgressBar(Asset::where('type', $type)->count());
            $deletedCount = 0;

            // source_modified_at gets passed to Deletion in Asset::createDeletion()
            Asset::where('type', $type)->select([
                'id',
                'source_modified_at',
            ])->chunk(200, function ($assets) use ($type, $bar, &$deletedCount) {
                $ids = $assets->pluck('id')->all();

                $result = Asset::instance()->callCheckPublished($type, $ids);

                if (count($result->result->results) === count($ids)) {
                    $bar->advance(count($ids));
                    return;
                }

                $returnedIds = Arr::pluck($result->result->results, 'id');
                $deletedIds = array_values(array_diff($ids, $returnedIds));

                foreach ($deletedIds as $deletedId) {
                    $asset = $assets->firstWhere('id', $deletedId);

                    if ($asset === null) {
                        continue;
                    }

                    $asset->delete();

                    $deletedCount += 1;
                }

                $bar->advance(count($ids));
            });

            $bar->finish();
            $this->output->newLine(1);

            $this->warn('Deleted ' . $deletedCount . ' ' . $type . 's');
            $this->output->newLine(1);
        }

    }
}
