<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\Deletion;

class DeleteReset extends AbstractCommand
{
    protected $signature = 'delete:reset';

    protected $description = 'Restore trashed assets and clear all deletions';

    public function handle()
    {
        Asset::onlyTrashed()->restore();
        Deletion::query()->delete();
    }
}
