<?php

namespace App\Console\Commands;

use App\Asset;

class ImportAssetsFull extends AbstractCommand
{

    protected $signature = 'import:assets-full';

    protected $description = "Import metadata about all assets from DAMS";

    public function handle()
    {
        foreach (Asset::$types as $type) {
            $page = 1;
            $assets = Asset::instance()->callGetAssets($type, $page);
            while ($assets->isNotEmpty()) {
                $assets->each(function ($item, $key) {
                    $item->save();
                });

                $page++;
                $assets = Asset::instance()->callGetAssets($type, $page);
            }
        }
    }

}
