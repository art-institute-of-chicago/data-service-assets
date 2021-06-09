<?php

namespace App\Http\Controllers;

use Aic\Hub\Foundation\AbstractController as BaseController;

class InvalidationsController extends BaseController
{

    protected $model = \App\Models\Invalidation::class;

    protected $transformer = \App\Http\Transformers\InvalidationTransformer::class;

}
