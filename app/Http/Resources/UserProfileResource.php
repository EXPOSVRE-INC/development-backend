<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
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
            'user_id' => $this->user_id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'birthDate' => $this->birthDate ? (string) $this->birthDate->format('Y-m-d') : '',
            'phone' => $this->phone != null ? $this->phone : "",
            'image' => $this->getFirstMediaUrl('preview'),
            'jobTitle' => (string) $this->jobTitle,
            'jobDescription' => (string) $this->jobDescription,
        ];
    }
}
