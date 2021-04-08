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
            Asset::where('type', $type)->select(['id'])->chunk(10, function($assets) use ($type) {
                $ids = $assets->pluck('id')->all();

                $result = Asset::instance()->callCheckPublished($type, $ids);

                if (count($result->result->results) === count($ids)) {
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

                    $this->info('Deleted ' . $deletedId);
                }
            });
        }

    }
}
