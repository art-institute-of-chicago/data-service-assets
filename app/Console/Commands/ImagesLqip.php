<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Asset;

class ImagesLqip extends AbstractCommand
{

    protected $signature = 'images:lqip';

    protected $description = 'Generates low quality image placeholders (LQIPs)';

    public function handle()
    {
        $images = Asset::images()
            ->select('id', 'lqip')
            ->whereNull('lqip')
            ->whereNull('image_lqiped_at')
            ->whereNotNull('image_downloaded_at');

        foreach ($images->cursor() as $image) {

            $id = $image->netx_uuid;
            $file = Asset::getImagePath($id);

            if ($image->lqip) {
                $this->warn($id . ' - ' . 'Already has LQIP');
                continue;
            }

            if (!file_exists($file)) {
                $this->warn($id . ' - ' . 'File not found');
                continue;
            }

            // Generate an Imagemagick command
            $cmd = sprintf('convert "%s" -resize x5 gif:- | base64 --wrap 0', $file);

            // Run the command and grab its output
            $lqip = exec($cmd);

            // Skip if the $lquip is blank
            if (empty($lqip)) {
                $this->warn($id . ' - ' . 'Cannot create LQIP');

                $image->image_lqiped_at = Carbon::now();
                $image->save();

                continue;
            }

            // Remove data:image/gif;base64,
            // $lqip = substr( $lqip, 22 );

            // Remove R0lGODlh (GIF magic number)
            // $lqip = substr( $lqip, 8 );

            // Prepend data:image/gif;base64,
            $lqip = 'data:image/gif;base64,' . $lqip;

            // Save the LQIP to database
            $image->lqip = $lqip;
            $image->image_lqiped_at = Carbon::now();
            $image->save();

            $this->info($id . ' - ' . 'Added LQIP');
        }

        $this->info($images->count() . ' image records processed.');
    }

}
