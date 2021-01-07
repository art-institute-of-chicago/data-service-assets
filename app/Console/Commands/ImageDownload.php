<?php

namespace App\Console\Commands;

use App\Asset;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ImageDownload extends AbstractCommand
{

    protected $signature = 'image:download {--all} {--skip-existing}';

    protected $description = 'Downloads all images from LAKE IIIF';

    private $sleep = 0;

    public function handle()
    {
        $images = Asset::images();
        $storage = Storage::disk('images');

        // Only get images that haven't been downloaded yet
        if (!$this->option('all')) {
            $images->whereNull('image_downloaded_at');
        }

        if (!$this->confirm($images->count() . ' images will be downloaded. Proceed?')) {
            return;
        }

        foreach ($images->cursor(['id']) as $image) {
            $id = $image->netx_uuid;
            $file = "images/{$id}.jpg";
            $url = config('source.iiif_url') . "/{$id}/full/843,/0/default.jpg";

            if ($storage->exists($file)) {
                if ($this->option('skip-existing')) {
                    $this->warn("{$id} - already exists – skipping!");
                    continue;
                }

                $image->image_attempted_at = null;
                $image->image_downloaded_at = null;
                $image->save();

                $storage->delete($file);

                $this->warn("{$id} - already exists – removed!");
            }

            $image->image_attempted_at = Carbon::now();
            $image->save();

            try {
                $contents = $this->fetch($url, $headers);
                $storage->put($file, $contents);

                $image->image_downloaded_at = Carbon::now();
                $image->save();

                $this->info("{$id} - downloaded");

                // Give the IIIF server a rest
                if (!in_array('x-cache: hit from cloudfront', array_map('strtolower', $headers))) {
                    usleep($this->sleep * 1000000);
                }
            } catch (\Exception $e) {
                // TODO: Avoid catching non-HTTP exceptions?
                $this->warn("{$id} - not found - {$url}");

                // Update the attempt date
                $image->save();

                continue;
            }
        }
    }

}
