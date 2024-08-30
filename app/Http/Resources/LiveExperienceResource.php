<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class LiveExperienceResource extends JsonResource
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
          'checkId' => $this->id,
          'content' => $this->name,
          'startUnixTime' => $this->startUnixTime == null ? 0 : Carbon::parse($this->startUnixTime)->timestamp,
          'finalUnixTime' => $this->finalUnixTime == null ? 0 : Carbon::parse($this->finalUnixTime)->timestamp,
        ];
    }
}
