<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Resources\Json\JsonResource;

class InterestsCategoryResource extends JsonResource
{

    public function toArray($request)
    {
        $slug = $this->slug;
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'image' => $this->getFirstMediaUrl('preview'),
            'post_count' => Post::whereHas('interests', function ($query) use ($slug) {
                return $query->where('slug', 'LIKE', '%'.$slug.'%');
            })->count()
        ];
    }
}
