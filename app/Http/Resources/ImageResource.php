<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
//            'link' => ($this->type == 'image') ? $this->original_url : route('video-stream', ['uuid' => $this->uuid]),
            'size' => $this->size
        ];

        if ($this->type == 'image') {
            $data['link'] = $this->original_url;
        } else if ($this->type == 'webp') {
            $data['link'] = $this->original_url;
        } else if ($this->type == 'video') {
            $data['link'] = $this->getUrl();
            $data['isVideo'] = true;
//            $data['video'] = route('video-stream', ['uuid' => $this->uuid]);
        }

        return $data;
    }
}
