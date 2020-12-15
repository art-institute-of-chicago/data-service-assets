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
            $result = Asset::instance()->callGetAssets($type, $page, $this->perPage, $this->since);

            while ($result['assets']->isNotEmpty()) {
                $this->info('Importing page ' . $result['page'] . ' of ' . $result['pages']);

                $result['assets']->each(function ($item, $key) {
                    $this->info($item->id . ' - ' . $item ->title);
                    $item->save();
                });

                $page++;
                $result = Asset::instance()->callGetAssets($type, $page, $this->perPage, $this->since);
            }
        }
    }

}
