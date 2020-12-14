<?php

namespace App\Console\Commands;

use App\Asset;

class ImportAssets extends AbstractCommand
{

    protected $signature = 'import:assets
                            {--since= : How far back to scan for records}';

    protected $description = "Import metadata about assets that changed since the last import";

    private $perPage = 10;

    public function handle()
    {
        $this->info('Looking for resources since ' . $this->since->toIso8601String());

        foreach (Asset::$types as $type) {
            $page = 1;
            $assets = Asset::instance()->callGetAssets($type, $page, $this->perPage, $this->since);
            while ($assets->isNotEmpty()) {
                $assets->each(function ($item, $key) {
                    $item->save();
                });

                $page++;
                $assets = Asset::instance()->callGetAssets($type, $page, $this->perPage, $this->since);
            }
        }
    }

}
