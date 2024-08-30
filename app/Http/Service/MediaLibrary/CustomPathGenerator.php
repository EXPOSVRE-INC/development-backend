<?php

namespace App\Http\Service\MediaLibrary;

use App\Models\InterestsCategory;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator
{
    public function getPath(Media $media) : string
    {
        if ($media->model instanceof User) {
            return 'user/' . $media->id . '/';
        } else if ($media->model instanceof Post) {
            return 'post/' . $media->id . '/';
        } else if ($media->model instanceof Tag) {
            return 'tag/' . $media->id . '/';
        } else if ($media->model instanceof InterestsCategory) {
            return 'interest/' . $media->id . '/';
        } else {
            return class_basename(get_class($media->model)) . '/' . $media->id . '/';
        }

    }

    public function getPathForConversions(Media $media) : string
    {
        return $this->getPath($media) . 'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive/';
    }
}
