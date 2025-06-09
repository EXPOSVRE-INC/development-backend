<?php

namespace App\Http\Resources;

use FFMpeg\FFMpeg;
use Spatie\MediaLibrary\Support\ImageFactory;
use Illuminate\Http\Resources\Json\JsonResource;

class PostImagePreviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // Initialize data array
        $data = [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->file_name,
            'size' => $this->size,
            'link' => $this->getFullUrl(),
        ];

        if ($this->type == 'video') {
            $data['isVideo'] = true;
        }
        return $data;
    }
}