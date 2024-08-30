<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostInterestsResource extends JsonResource
{

    public function toArray($request)
    {
        return $this->name;
    }
}
