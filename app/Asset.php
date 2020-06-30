<?php

namespace App;

use Aic\Hub\Foundation\AbstractModel;
use Carbon\Carbon;

class Asset extends AbstractModel
{

    use SourceCallable, Singletonable;

    protected $dates = [
        'source_modified_at',
    ];

    protected $casts = [
        'publish_status' => 'array',
    ];

    public static $types = [
        'Image',
        'Document',
    ];


    public function callGetAssets($type = 'Image', $page = 1)
    {
        $authKey = $this->authenticate();
        $request = $this->buildQuery($authKey, $type, $page);
        $response = $this->call($request);
        $results = $this->parseResult($response);

        return $results;

    }

    private function parseResult($response)
    {
        if (!$response) {
            return [];
        }
        $response = json_decode($response);

        $assets = [];

        foreach ($response->result->results as $res) {
            $asset = Asset::findOrNew($res->id);
            $asset->fillFrom($res);
            $assets[] = $asset;
        }

        return collect($assets);
    }

    public function fillFrom($source)
    {
        $this->id = $source->id;
        $this->type = head($source->attributes->assetType_pub);
        $this->file_name = $source->file->name;
        $this->file_size = $source->file->size;
        $this->external_website = head($source->attributes->{'Related website'});
        $this->alt_text = head($source->attributes->{'Alt tag'});
        $this->publish_status = $source->attributes->{'Publish status'};
        $this->source_modified_at = new Carbon(head($source->attributes->modificationDate_pub), 'America/Chicago') ?? null;
    }


    private function buildQuery($authKey, $type, $page = 1)
    {
        $request = [
            'id' => 'callGetAssets__data-service-assets__' . config('app.env') . '__' . date("Y-m-d_H:i:s"),
            'method' => 'getAssetsByQuery',
            'params' => [
                $authKey,
                [
                    'query' => [
                        [
                            'operator' => 'and',
                            'exact' => [
                                'attribute' => 'Publish status',
                                'value' => 'Web'
                            ]
                        ],
                        [
                            'operator' => 'and',
                            'exact' => [
                                'attribute' => 'assetType_pub',
                                'value' => $type
                            ]
                        ]
                    ]
                ],
                [
                    'sort' => [
                        'field' => 'modificationDate_pub',
                        'order' => 'desc'
                    ],
                    'page' => [
                        'startIndex' => ($page - 1) * 10,
                        'size' => 10
                    ],
                    'data' => [
                        'asset.base',
                        'asset.attributes',
                        'asset.relatedFolders',
                        'asset.file'
                    ]
                ]
            ],
            'dataContext' => 'json',
            'jsonrpc' => '2.0'
        ];

        return json_encode($request);
    }
}
