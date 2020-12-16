<?php

namespace App\Http\Controllers;

use Aic\Hub\Foundation\AbstractController as BaseController;

class ImagesController extends BaseController
{

    protected $model = \App\Asset::class;

    protected $transformer = \App\Http\Transformers\ImageTransformer::class;

}
