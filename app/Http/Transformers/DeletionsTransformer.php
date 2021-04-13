<?php

namespace App\Http\Transformers;

class DeletionsTransformer extends AbstractTransformer
{
    public function transform($item)
    {
        $data = [
            'id' => $item->id,
            'asset_id' => $item->asset_id,
            'source_deleted_at' => $this->getDateValue($item, 'source_deleted_at'),
            'created_at' => $this->getDateValue($item, 'created_at'),
            'modified_at' => $this->getDateValue($item, 'updated_at'),
        ];

        // Enables ?fields= functionality
        return parent::transform($data);
    }
}
