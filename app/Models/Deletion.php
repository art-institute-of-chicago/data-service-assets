<?php

namespace App\Models;

use App\Models\Behaviors\HasUuid;

use Aic\Hub\Foundation\AbstractModel;

class Deletion extends AbstractModel
{

    use HasUuid;

    protected $dates = [
        'source_deleted_at',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

}
