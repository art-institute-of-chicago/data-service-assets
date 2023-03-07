<?php

namespace App\Console\Commands;

use Aic\Hub\Foundation\Console\Concerns\HasSince;
use App\Models\Asset;

class ImportAssets extends AbstractCommand
{
    use HasSince;

    protected $signature = 'import:assets';

    protected $description = 'Import metadata about assets that changed since the last import';

    private $perPage = 10;

    public function handle()
    {
        foreach (Asset::$types as $type) {
            $page = 1;
            $result = Asset::instance()->callGetAssets($type, $page, $this->perPage, $this->since);

            if ($result['assets']->isEmpty()) {
                $this->info('Nothing new found with type ' . $type);
            } else {
                $this->info('Found new items with type ' . $type);
            }

            while ($result['assets']->isNotEmpty()) {
                $this->info('Importing page ' . $result['page'] . ' of ' . $result['pages']);

                $result['assets']->each(function ($item, $key) {
                    $this->info($item->id . ' - ' . $item->title);
                    $item->save();
                });

                $page++;
                $result = Asset::instance()->callGetAssets($type, $page, $this->perPage, $this->since);
            }
        }
    }

}
