<?php

namespace App\Http\Resources;

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
        $data = [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->file_name,
            'size' => $this->size,
            'link' => $this->getUrl('small'),
        ];

        if ($this->type == 'video') {
            $data['isVideo'] = true;
        }

        return $data;
    }
}
