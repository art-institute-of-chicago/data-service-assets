<?php

namespace App\Http\Controllers;

use App\Foo;
use App\Bar;

use Illuminate\Http\Request;

use Aic\Hub\Foundation\AbstractController as BaseController;

class AssetsController extends BaseController
{

    protected $model = \App\Asset::class;

    protected $transformer = \App\Http\Transformers\AssetTransformer::class;

}
