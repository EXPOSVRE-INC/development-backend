<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Resources\Json\JsonResource;

class TagsSimpleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => isset(json_decode($this->name)->en) ? json_decode($this->name)->en : $this->name,
            'slug' => isset(json_decode($this->slug)->en) ? json_decode($this->slug)->en : $this->slug,
            'image' => $this->getFirstMediaUrl('preview'),
        ];
    }
}
