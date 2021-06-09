<?php

namespace App\Http\Controllers;

use App\Models\Invalidation;
use Illuminate\Http\Request;
use Aic\Hub\Foundation\Exceptions\DetailedException;

use Aic\Hub\Foundation\AbstractController as BaseController;

class InvalidationsController extends BaseController
{

    protected $model = \App\Models\Invalidation::class;

    protected $transformer = \App\Http\Transformers\InvalidationTransformer::class;

    public function create(Request $request)
    {
        if (empty($request->asset_ids)) {
            throw new DetailedException('Missing parameter', 'Expecting asset_ids array', 400);
        }

        $existingInvalidations = Invalidation::query()
            ->whereIn('asset_id', $request->asset_ids)
            ->get();

        $existingAssetIds = $existingInvalidations
            ->pluck('asset_id')
            ->unique();

        $newAssetIds = collect($request->asset_ids)
            ->diff($existingAssetIds)
            ->values();

        $newInvalidations = collect([]);

        $newAssetIds->each(function($assetId) use (&$newInvalidations) {
            $invalidation = Invalidation::create([
                'asset_id' => $assetId,
            ]);

            $newInvalidations->push($invalidation);
        });

        $allInvalidations = $existingInvalidations
            ->merge($newInvalidations)
            ->sortBy('modified_at')
            ->reverse()
            ->values();

        return response()->json($allInvalidations);
    }

}
