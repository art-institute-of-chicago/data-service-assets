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
            'source_modified_at' => $this->getDateValue($asset, 'source_modified_at'),
            'created_at' => $this->getDateValue($asset, 'created_at'),
            'modified_at' => $this->getDateValue($asset, 'updated_at'),
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

    protected function getDateValue($asset, $fieldName)
    {
        if (!isset($asset->{$fieldName})) {
            return null;
        }

        $date = $asset->{$fieldName};
        $date->setTimezone('America/Chicago');

        return $date->toIso8601String();
    }
}
