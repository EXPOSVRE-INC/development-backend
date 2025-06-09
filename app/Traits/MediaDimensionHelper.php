<?php

namespace App\Traits;

use FFMpeg\FFMpeg;
use Spatie\MediaLibrary\Support\ImageFactory;
use Exception;

trait MediaDimensionHelper
{
    public function getImageDimensionsFromStream($url)
    {
        try {
            $image = ImageFactory::load(file_get_contents($url));
            return [
                'width' => $image->getWidth(),
                'height' => $image->getHeight(),
            ];
        } catch (\Exception $e) {
            return ['width' => 0, 'height' => 0];
        }
    }

    public function getVideoDimensionsFromStream($url)
    {
        try {
            $ffmpeg = FFMpeg::create();
            $video = $ffmpeg->open($url);
            $dimension = $video->getStreams()->videos()->first()->getDimensions();
            return [
                'width' => $dimension->getWidth(),
                'height' => $dimension->getHeight(),
            ];
        } catch (\Exception $e) {
            return ['width' => 0, 'height' => 0];
        }
    }
}
