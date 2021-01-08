<?php

namespace App;

use Aic\Hub\Foundation\AbstractModel;
use Carbon\Carbon;

class Asset extends AbstractModel
{

    use SourceCallable, Singletonable;

    protected $dates = [
        'image_attempted_at',
        'image_downloaded_at',
        'source_modified_at',
    ];

    protected $casts = [
        'publish_status' => 'array',
        'color' => 'object',
    ];

    public static $types = [
        'Image',
        'Document',
        'Audio',
        'Video'
    ];

    public function scopeImages($query)
    {
        return $query->where('type','Image');
    }

    public function scopeTexts($query)
    {
        return $query->where('type','Document');
    }

    public function scopeSounds($query)
    {
        return $query->where('type','Audio');
    }

    public function scopeVideos($query)
    {
        return $query->where('type','Video');
    }

    public function getNetxUuidAttribute($value)
    {
        $hash = (string) hash('md5', config('source.asset_prefix') . $this->id);
        return substr($hash, 0, 8)  . '-'
          . substr($hash, 8, 4)  . '-'
          . substr($hash, 12, 4) . '-'
          . substr($hash, 16, 4) . '-'
          . substr($hash, 20);
    }

    public function callGetAssets(string $type, int $page, int $perPage, Carbon $since)
    {
        $authKey = $this->authenticate();
        $request = $this->buildQuery($authKey, $type, $page, $perPage, $since);
        $response = $this->call($request);
        $results = $this->parseResult($response);

        $results['page'] = $page;
        $results['pages'] = ceil($results['size'] / $perPage);

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

        return [
            'size' => $response->result->size,
            'assets' => collect($assets),
        ];
    }

    public function fillFrom($source)
    {
        $this->id = $source->id;
        $this->title = $source->name;
        $this->type = $this->head($source->attributes->assetType_pub);
        $this->checksum = $source->file->checksum;
        $this->width = $source->file->width;
        $this->height = $source->file->height;
        $this->external_website = $this->head($source->attributes->{'Related website'});
        $this->alt_text = $this->head($source->attributes->{'Alt tag'});
        $this->publish_status = $source->attributes->{'Publish status'};
        $this->copyright_notice = $this->head($source->attributes->{'Copyright notice'});
        $this->source_modified_at = isset($source->modDate) ? Carbon::createFromTimestamp($source->modDate/1000) : null;
    }

    private function head($array = []) {
        if (empty($array)) {
            return null;
        }
        return head($array);
    }

    private function buildQuery(string $authKey, string $type, int $page, int $perPage, Carbon $since)
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
                        ],
                        [
                            'operator' => 'and',
                            'range' => [
                                'field' => 'modDate',
                                'min' => $since->timestamp * 1000, // milliseconds, not seconds
                                'max' => null,
                                'includeMin' => true,
                                'includeMax' => false,
                            ]
                        ],
                    ]
                ],
                [
                    'sort' => [
                        'field' => 'modDate',
                        'order' => 'desc'
                    ],
                    'page' => [
                        'startIndex' => ($page - 1) * $perPage,
                        'size' => $perPage,
                    ],
                    'data' => [
                        'asset.base',
                        'asset.attributes',
                        // TODO: Do we need this? Opportunity to make response lighter?
                        // 'asset.folders',
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
