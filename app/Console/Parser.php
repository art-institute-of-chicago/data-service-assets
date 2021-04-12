<?php

namespace App\Console;

use Illuminate\Console\Parser as BaseParser;

class Parser extends BaseParser
{
    /**
     * Make this public so we can use it in our traits to add options.
     */
    public static function parseOption($token)
    {
        return parent::parseOption($token);
    }
}
