<?php

namespace App\Http\Transformers;

use Aic\Hub\Foundation\AbstractTransformer as BaseTransformer;

class AbstractTransformer extends BaseTransformer
{
    protected function getDateValue($item, $fieldName)
    {
        if (!isset($item->{$fieldName})) {
            return null;
        }

        $date = $item->{$fieldName};
        $date->setTimezone('America/Chicago');

        return $date->toIso8601String();
    }
}
