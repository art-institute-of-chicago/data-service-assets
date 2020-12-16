<?php

namespace App\Http\Transformers;

use Aic\Hub\Foundation\AbstractTransformer;

class AssetTransformer extends AbstractTransformer
{
    public function transform($asset)
    {
        $sharedFields = [
            'id' => $asset->id,
            'title' => $asset->title,
            'content' => $asset->external_website,
            'alt_text' => $asset->alt_text,
            'is_educational_resource' => collect($asset->publish_status)->contains('Educational Resources'),
            'is_multimedia_resource' => collect($asset->publish_status)->contains('Multimedia'),
            'is_teacher_resource' => collect($asset->publish_status)->contains('Teacher Resources'),
            'content_e_tag' => $asset->checksum,
            'credit_line' => $asset->copyright_notice,
        ];

        $typeFields = $this->transformAsset($asset);

        $dateFields = [
            'source_modified_at' => $asset->source_modified_at->toIso8601String(),
            'created_at' => $asset->created_at->toIso8601String(),
            'modified_at' => $asset->updated_at->toIso8601String(),
        ];

        $data = array_merge($sharedFields, $typeFields, $dateFields);

        // Enables ?fields= functionality
        return parent::transform($data);
    }

    /**
     * Override this in child classes.
     */
    protected function transformAsset($asset)
    {
        return [];
    }

}
