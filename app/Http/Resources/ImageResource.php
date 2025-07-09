<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\ImageFactory;
use FFMpeg\FFMpeg;
use Exception;

class ImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'name' => $this->file_name,
            'size' => $this->size,
        ];

        $media = Media::where('uuid', $this->uuid)->first();
        $post = Post::where('id', $media->model_id)->first();

        if ($post) {
            if (is_null($post->song_id) && $this->type == 'video') {
                $files = Storage::disk('public')->files($this->id);
                $originalFile = null;

                foreach ($files as $file) {
                    if (strpos(basename($file), 'original') === 0) {
                        $originalFile = $file;
                        break;
                    }
                }

                $data['isVideo'] = true;
                if ($originalFile) {
                    $data['link'] = url('storage/' . $originalFile);
                } else {
                    $data['link'] = $this->getUrl();
                }

                $data['thumb'] = $this->hasGeneratedConversion('header_video')
                    ? $this->getUrl('header_video')
                    : $this->getUrl('original');
            } else {
                if ($this->type == 'video') {
                    $data['isVideo'] = true;
                    $data['link'] = $this->getUrl();
                    $data['thumb'] = $this->hasGeneratedConversion('header_video')
                        ? $this->getUrl('header_video')
                        : $this->getUrl('original');
                }
            }
        }

        if ($this->type == 'image' || $this->type == 'webp') {
            $data['link'] = $this->original_url;

            $mediaPath = $this->getPath('original');
            if (str_contains($this->mime_type, 'image/webp')) {
                $data['image_width'] = 160;
                $data['image_height'] = 160;
            } elseif (!empty($mediaPath) && file_exists($mediaPath)) {
                $image = ImageFactory::load($mediaPath);
                $data['image_height'] = $image->getHeight();
                $data['image_width'] = $image->getWidth();
            } else {
                $data['image_width'] = 0;
                $data['image_height'] = 0;
            }
        }

        if ($this->type == 'video') {
            $mediaPath = $this->getPath('original');
            if (!empty($mediaPath) && file_exists($mediaPath)) {
                try {
                    $ffmpeg = FFMpeg::create();
                    $video = $ffmpeg->open($mediaPath);
                    $dimension = $video->getStreams()->videos()->first()->getDimensions();
                    $data['image_width'] = $dimension->getWidth();
                    $data['image_height'] = $dimension->getHeight();
                } catch (\Exception $e) {
                    $data['image_width'] = 0;
                    $data['image_height'] = 0;
                }
            } else {
                $data['image_width'] = 0;
                $data['image_height'] = 0;
            }
        }


        return $data;
    }
}
