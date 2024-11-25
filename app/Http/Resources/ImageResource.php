<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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

            if ($originalFile) {
                $data['link'] = url('storage/' . $originalFile);
            } else {
                $data['link'] = $this->getUrl();
                $data['isVideo'] = true;
                $data['thumb'] = $this->hasGeneratedConversion('thumb')
                    ? $this->getUrl('thumb')
                    : $this->getUrl('original');
            }
        } else {
            // If song_id is not null, return the video URL directly
            if ($this->type == 'video') {
                $data['link'] = $this->getUrl();
                $data['isVideo'] = true;
                $data['thumb'] = $this->hasGeneratedConversion('thumb')
                    ? $this->getUrl('thumb')
                    : $this->getUrl('original');
            }
        }
    }

    // Handle other media types (e.g., images or webp)
    if ($this->type == 'image' || $this->type == 'webp') {
        $data['link'] = $this->original_url;
    }

    return $data;
}

}
