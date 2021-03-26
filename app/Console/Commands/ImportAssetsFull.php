<?php

namespace App\Console\Commands;

use App\Models\Command;

class ImportAssetsFull extends ImportAssets
{

    protected $signature = 'import:assets-full';

    protected $description = "Import metadata about all assets from DAMS";

    public function handle()
    {
        $this->since = Command::never();

        parent::handle();
    }

}
