<?php

namespace App\Console\Commands;

use Aws\CloudFront\CloudFrontClient;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Models\Asset;
use App\Models\Invalidation;

class Invalidate extends AbstractCommand
{
    protected $signature = 'invalidate';

    protected $description = 'Process all outstanding invalidations';

    private $client;

    public function handle()
    {
        $this->client = $this->getClient();

        if ($this->hasInProgressInvalidation()) {
            $this->info('Waiting on an invalidation to finish');
            return;
        }

        // https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/Invalidation.html#InvalidationLimits
        // With the * wildcard, we can have requests for up to 15 invalidation paths in progress at one time.
        $invalidations = Invalidation::query()
            // IMG-35: Allow IIIF server 5 min to process each image
            ->where('updated_at', '<', Carbon::now()->subMinutes(5))
            ->limit(15)
            ->get();

        if ($invalidations->count() < 1) {
            $this->info('No invalidations outstanding');
            return;
        }

        // WEB-1858: Consider storing URLs instead of ids?
        $urls = $invalidations
            ->pluck('asset_id')
            ->map(function($assetId) {
                return '/iiif/2/' . Asset::getHashedId($assetId) . '/*';
            })
            ->all();

        $this->createInvalidationRequest($urls);

        $this->info('Scheduled an invalidation for the following URLs:');

        foreach ($urls as $url) {
            $this->info($url);
        }

        $invalidations->each(function($invalidation) {
            $invalidation->delete();
        });
    }

    private function getClient()
    {
        return new CloudFrontClient([
            'region' => config('cloudfront.region'),
            'version' => config('cloudfront.sdk_version'),
            'credentials' => [
                'key' => config('cloudfront.key'),
                'secret' => config('cloudfront.secret'),
            ],
            'http' => [
                'proxy' => config('cloudfront.http_proxy'),
            ]
        ]);
    }

    private function hasInProgressInvalidation()
    {
        $list = $this->client
            ->listInvalidations([
                'DistributionId' => config('cloudfront.distribution')
            ])
            ->get('InvalidationList');

        if (isset($list['Items']) && !empty($list['Items'])) {
            return Collection::make($list['Items'])->where('Status', 'InProgress')->count() > 0;
        }

        return false;
    }

    private function createInvalidationRequest($paths = [])
    {
        return $this->client->createInvalidation([
            'DistributionId' => config('cloudfront.distribution'),
            'InvalidationBatch' => [
                'Paths' => [
                    'Quantity' => count($paths),
                    'Items' => $paths,
                ],
                'CallerReference' => time(),
            ],
        ]);
    }
}
