<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Traits\MediaDimensionHelper;

class ImageResource extends JsonResource
{
    use MediaDimensionHelper;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    { // or log($this)

        $data = [
            'uuid' => $this->uuid,
            'name' => $this->file_name,
            'size' => $this->size,
        ];

        $media = Media::where('uuid', $this->uuid)->first();
        $post = Post::where('id', $media->model_id)->first();

        if ($post) {
            if (is_null($post->song_id) && $this->type == 'video') {

                $data['isVideo'] = true;
                $data['link'] = $this->getUrl('original') ?? $this->getUrl();

                $data['thumb'] = $this->hasGeneratedConversion('thumb')
                    ? $this->getUrl('thumb')
                    : $this->getUrl('original');
            } else {
                if ($this->type == 'video') {
                    $data['isVideo'] = true;
                    $data['link'] = $this->getUrl();
                    $data['thumb'] = $this->hasGeneratedConversion('thumb')
                        ? $this->getUrl('thumb')
                        : $this->getUrl('original');
                }
            }
        }

        if ($this->type == 'image' || $this->type == 'webp') {
            $data['link'] = $this->getUrl('original') ?? $this->getUrl();
            if (str_contains($this->mime_type, 'image/webp')) {
                $data['image_width'] = 160;
                $data['image_height'] = 160;
            } else {
                $dimensions = $this->getImageDimensionsFromStream($this->getUrl('original'));
                $data['image_width'] = $dimensions['width'];
                $data['image_height'] = $dimensions['height'];
            }
        }

        if ($this->type === 'video') {
            $dimensions = $this->getVideoDimensionsFromStream($this->getUrl('original'));
            $data['image_width'] = $dimensions['width'];
            $data['image_height'] = $dimensions['height'];
        }

        return $data;
    }
}
