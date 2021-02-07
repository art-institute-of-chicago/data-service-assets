<?php

namespace App\Http\Transformers;

class ImageTransformer extends AssetTransformer
{
    protected function transformAsset($asset)
    {
        return [
            // From NetX
            'width' => $asset->width,
            'height' => $asset->height,

            // From Python subservice
            'ahash' => $asset->ahash,
            'phash' => $asset->phash,
            'dhash' => $asset->dhash,
            'whash' => $asset->whash,
            'colorfulness' => $asset->colorfulness,

            // From artisan commands
            'color' => $asset->color,
            'lqip' => $asset->lqip,

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
