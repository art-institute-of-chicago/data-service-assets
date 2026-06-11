<?php

namespace App\Console\Commands;

use Aws\CloudFront\CloudFrontClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Asset;
use App\Models\Invalidation;
use Monolog\Utils;

class Invalidate extends AbstractCommand
{
    protected $signature = 'invalidate';

    protected $description = 'Process all outstanding invalidations';

    private $cloudFrontClient;

    public function handle()
    {
        $this->cloudFrontClient = $this->getCloudFrontClient();

        if ($this->hasInProgressInvalidation()) {
            $this->info('Waiting on an invalidation to finish');
            return;
        }

        // https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/Invalidation.html#InvalidationLimits
        // With the * wildcard, we can have requests for up to 15 invalidation paths in progress at one time.
        $invalidations = Invalidation::query()
            // IMG-35: Allow IIIF server 10 min to process each image
            ->where(function ($query) {
                $query->where('priority', '<', 1);
                $query->where('updated_at', '<', Carbon::now()->subMinutes(10));
            })
            // ...but don't wait for invalidations submitted through the endpoint
            ->orWhere('priority', '>', 0)
            // Process highest priority first, then oldest first
            ->orderBy('priority', 'desc')
            ->orderBy('updated_at', 'asc')
            ->limit(15)
            ->get();

        if ($invalidations->count() < 1) {
            $this->info('No invalidations outstanding');
            return;
        }

        // WEB-1858: Consider storing URLs instead of ids?
        $urls = $invalidations
            ->pluck('asset_id')
            ->map(function ($assetId) {
                return '/iiif/2/' . Asset::getHashedId($assetId) . '/*';
            })
            ->all();

        $this->createInvalidationRequest($urls);

        $this->info('Scheduled an invalidation for the following URLs:');

        foreach ($urls as $url) {
            $this->info($url);
        }

        $invalidations->each(function ($invalidation) {
            $invalidation->delete();
        });
    }

    private function getCloudFrontClient()
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
        $list = $this->cloudFrontClient
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
        $this->cloudFrontClient->createInvalidation([
            'DistributionId' => config('cloudfront.distribution'),
            'InvalidationBatch' => [
                'Paths' => [
                    'Quantity' => count($paths),
                    'Items' => $paths,
                ],
                'CallerReference' => time(),
            ],
        ]);

        try {
            $postData = [
                'files' => $paths,
            ];
            $postString = Utils::jsonEncode($postData);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://api.cloudflare.com/client/v4/zones/' . config('cloudflare.zone_id') . '/purge_cache');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "Authorization: Bearer " . config('cloudflare.key'),
            ]);

            ob_start();
            curl_exec($ch);
            curl_close($ch);
            $string = ob_get_contents();
            ob_end_clean();

            $res = json_decode($string);

            if (!$res->success) {
                Log::debug('Cloudflare purge returned with a failed response');
            }
        } catch (\Exception) {
            Log::debug('Cloudflare purge failed in making HTTP API request');
        }
    }
}
