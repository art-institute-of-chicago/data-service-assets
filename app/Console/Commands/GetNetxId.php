<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GetNetxId extends Command
{
    protected $signature = 'get:netx-id {hash}';

    protected $description = 'Determine a NetX ID by its image hash';

    public function handle()
    {
        $hash = Str::remove('-', $this->argument('hash'));

        $hashed = '';
        $i = 100000;
        while ($hashed != $hash) {
            if ($i % 10000 == 0) {
                $this->info("Checked up to " . $i);
            }
            $hashed = (string) hash('md5', config('source.uuid_prefix') . $i++);
        }
        $this->comment("Got it! " . --$i);
    }
}
