<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\HasSince;
use App\Models\Asset;
use Carbon\Carbon;

class DeletePartial extends AbstractCommand
{
    use HasSince;

    protected $signature = 'delete:partial';

    protected $description = 'Import delete entries published by the content shim';

    public function handle()
    {
        $url = config('source.shim_api_url') . '/assets/unpublished_assets?' . http_build_query([
            'since' => $this->since->toIso8601String(),
        ]);

        $results = json_decode($this->fetch($url));

        foreach ($results as $result) {
            $asset = Asset::find($result->netx_asset_id);

            if (!$asset) {
                $this->info($result->netx_asset_id . ' not found');
                continue;
            }

            $deletedAt = new Carbon($result->netx_modified);
            $deletedAt->timezone = 'America/Chicago';

            // Equal to catch any unpublished items we might have imported
            if ($asset->source_modified_at->lte($deletedAt)) {
                $this->warn($asset->id . ' deleted');
                $asset->delete();
            } else {
                $this->info($asset->id . ' newer than delete');
            }
        }
    }

    private function fetch($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // WEB-874: If connection or response take longer than 5 seconds, give up
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($ch);

        if ($result === false) {
            throw new \Exception('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        return $result;
    }
}
