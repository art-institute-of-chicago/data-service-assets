<?php

namespace App\Http\Transformers;

class ImageTransformer extends AssetTransformer
{
    protected function transformAsset($asset)
    {
        return [
            'width' => $asset->width,
            'height' => $asset->height,
        ];
    }
}
