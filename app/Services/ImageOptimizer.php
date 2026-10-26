<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImageOptimizer
{
    /**
     * Optimize an uploaded image using GD (resize to max 1600px, convert to webp) and store it.
     */
    public static function optimizeAndStore(UploadedFile|TemporaryUploadedFile $file, string $directory = 'projects', string $disk = 'public', int $maxDimension = 1600): ?string
    {
        try {
            $realPath = $file->getRealPath();
            if (! $realPath || ! file_exists($realPath)) {
                return null;
            }

            $rawContents = file_get_contents($realPath);
            if (! $rawContents) {
                return null;
            }

            $image = @imagecreatefromstring($rawContents);
            if (! $image) {
                return $file->storePublicly($directory, $disk);
            }

            $width = imagesx($image);
            $height = imagesy($image);

            // Scale down if either dimension exceeds maxDimension
            if ($width > $maxDimension || $height > $maxDimension) {
                if ($width >= $height) {
                    $newWidth = $maxDimension;
                    $newHeight = (int) round($height * ($maxDimension / $width));
                } else {
                    $newHeight = $maxDimension;
                    $newWidth = (int) round($width * ($maxDimension / $height));
                }

                $scaled = imagescale($image, $newWidth, $newHeight);
                if ($scaled) {
                    imagedestroy($image);
                    $image = $scaled;
                }
            }

            imagealphablending($image, false);
            imagesavealpha($image, true);

            ob_start();
            imagewebp($image, null, 85);
            $webpData = ob_get_clean();
            imagedestroy($image);

            if (! $webpData) {
                return $file->storePublicly($directory, $disk);
            }

            $filename = Str::random(40).'.webp';
            $path = trim($directory.'/'.$filename, '/');

            Storage::disk($disk)->put($path, $webpData);

            return $path;
        } catch (\Throwable $e) {
            return $file->storePublicly($directory, $disk);
        }
    }
}
