<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InterestsResource extends JsonResource
{

    public function toArray($request)
    {
        return $this->id;
    }
}
