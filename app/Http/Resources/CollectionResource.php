<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CollectionResource extends JsonResource
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
            'title' => $this->name,
            'description' => $this->description,
            'allowToComment' => (bool) $this->allowToComment,
            'allowToCrown' => (bool) $this->allowToCrown,
            'ownerId' => $this->user_id,
            'image' => $this->getFirstMediaUrl('files'),
            'posts' => $this->posts->count(),
            'crowns' => $this->likers()->count()
        ];
    }
}
