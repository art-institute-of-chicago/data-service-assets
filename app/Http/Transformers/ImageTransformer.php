<?php

namespace App\Http\Transformers;

class ImageTransformer extends AssetTransformer
{
    protected function transformAsset($asset)
    {
        return [
            'width' => $asset->width,
            'height' => $asset->height,

            'image_attempted_at' => $this->getDateValue($asset, 'image_attempted_at'),
            'image_downloaded_at' => $this->getDateValue($asset, 'image_downloaded_at'),
        ];
    }

    private function getDateValue($image, $fieldName)
    {
        if (!isset($image->{$fieldName})) {
            return null;
        }

        $date = $image->{$fieldName};
        $date->setTimezone('America/Chicago');

        return $date->toIso8601String();
    }
}
