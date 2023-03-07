<?php

namespace App\Console\Commands;

use App\Models\Deletion;

class DeleteFix extends AbstractCommand
{
    protected $signature = 'delete:fix';

    protected $description = 'Set source_deleted_at for all deletions to source_modified_at of their assets';

    public function handle()
    {
        foreach (Deletion::cursor() as $deletion) {
            if ($deletion->source_deleted_at) {
                $this->info('Deletion ' . $deletion->id . ' already has deletion date');
                continue;
            }

            $asset = $deletion->asset()->onlyTrashed()->first();

            if (!$asset) {
                $this->info('Deletion ' . $deletion->id . ' has no valid asset');
                continue;
            }

            if (!$asset->source_modified_at) {
                $this->info('Deletion ' . $deletion->id . ' has asset ' . $asset->id . ', but it has no modified date');
                continue;
            }

            if ($asset->source_modified_at->gt($deletion->updated_at)) {
                $this->info('Deletion ' . $deletion->id . ' has asset ' . $asset->id . ', but its modified date is too recent');
                continue;
            }

            $deletion->source_deleted_at = $asset->source_modified_at;
            $deletion->save();

            $this->warn('Deletion ' . $deletion->id . ' copied date from asset ' . $asset->id . ': ' . $deletion->source_deleted_at->toISOString());
        }
    }
}
