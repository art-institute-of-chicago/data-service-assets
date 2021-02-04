<?php

namespace App\Console\Commands;

use Intervention\Image\ImageManager;
use Intervention\Image\Exception\NotSupportedException;
use marijnvdwerf\palette\Palette;

use App\Asset;
use Carbon\Carbon;

class ImagesColor extends AbstractCommand
{

    protected $signature = 'images:color';

    protected $description = 'Determine dominant color for each image';

    public function handle()
    {
        $manager = new ImageManager(['driver' => 'imagick']);

        $images = Asset::images()
            ->select('id')
            ->whereNull('color')
            ->whereNull('image_colored_at')
            ->whereNotNull('image_downloaded_at');

        foreach ($images->cursor() as $image) {

            $id = $image->netx_uuid;
            $file = Asset::getImagePath($id);

            if (!file_exists($file)) {
                $this->warn("{$id} - File not found ({$file})");
                continue;
            }

            $contents = file_get_contents($file);

            try {
                $palette = Palette::generate($manager->make($contents));
            } catch (NotSupportedException $e) {
                throw $e;
            } catch (\Exception $e) {
                // TODO: Resolve [ErrorException]  max(): Array must contain at least one element
                // See vendor/marijnvdwerf/material-palette/src/Palette.php:81
                // https://github.com/marijnvdwerf/material-palette-php/issues/6
                $this->warn("{$id} - Monotone image skipped");
                $image->color = null;
                $image->image_colored_at = Carbon::now();
                $image->save();
                continue;
            }

            // TODO: Reorder these for better results?
            $swatches = collect([
                'vibrant' => $palette->getVibrantSwatch(),
                'muted' => $palette->getMutedSwatch(),
                'vibrant_light' => $palette->getLightVibrantSwatch(),
                'muted_light' => $palette->getLightMutedSwatch(),
                'vibrant_dark' => $palette->getDarkVibrantSwatch(),
                'muted_dark' => $palette->getDarkMutedSwatch(),
            ]);

            // Select the first swatch that (1) isn't empty, and (2) isn't derived
            $swatch = $swatches->first(function($swatch) {
                return !is_null($swatch) && $swatch->getPopulation() > 0;
            });

            // This might happen if the image is black-and-white?
            if (!$swatch) {
                $this->warn("{$id} - No swatches generated");
                $image->color = null;
                $image->image_colored_at = Carbon::now();
                $image->save();
                continue;
            }

            // Convert to HSL - better for searching w/ Elasticsearch:
            // https://dpb587.me/blog/2014/04/24/color-searching-with-elasticsearch.html
            $color = $swatch->getColor()->asHSLColor();

            // For calculating percentage of pixel population
            $size = getimagesize($file);

            // @TODO Consider using HSV instead
            $out = [
                'population' => $swatch->getPopulation(),
                'percentage' => $swatch->getPopulation() / ($size[0] * $size[1]) * 100,
                'h' => floor($this->normalize($color->getHue() * 360, 0, 360)),
                's' => floor($this->normalize($color->getSaturation() * 100, 0, 100)),
                'l' => floor($this->normalize($color->getLightness() * 100, 0, 100)),
            ];

            // Save the generated color to database
            $image->color = $out;
            $image->image_colored_at = Carbon::now();
            $image->save();

            $this->info($id . ' - ' . json_encode($out));
        }
    }

    /**
     * Normalizes any number to an arbitrary range by assuming the range
     * wraps around when going below min or above max.
     *
     * @link https://stackoverflow.com/questions/1628386/normalise-orientation-between-0-and-360
     */
    private function normalize($value, $min, $max)
    {
        $range = $max - $min;
        $offset = $value - $min;

        // + start to reset back to start of original range
        return ($offset - (floor($offset / $range) * $range)) + $min;
    }

}
