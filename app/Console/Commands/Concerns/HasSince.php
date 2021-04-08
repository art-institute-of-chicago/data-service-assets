<?php

namespace App\Console\Commands\Concerns;

use App\Console\Parser;

trait HasSince
{
    protected function initHasSince()
    {
        $this->getDefinition()->addOptions([
            Parser::parseOption('since= : How far back to scan for records'),
            Parser::parseOption('full : Import records since the beginning of time'),
        ]);
    }
}
