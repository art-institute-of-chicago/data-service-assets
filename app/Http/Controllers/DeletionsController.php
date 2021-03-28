<?php

namespace App\Http\Controllers;

use Aic\Hub\Foundation\AbstractController as BaseController;

class DeletionsController extends BaseController
{

    protected $model = \App\Models\Deletion::class;

    protected $transformer = \App\Http\Transformers\ActionTransformer::class;

}
