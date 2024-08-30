<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserWithShippingAddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'name' => $this->profile->firstName . ' ' . $this->profile->lastName,
            'email' => $this->email,
            'phone' => $this->profile->phone != null ? $this->profile->phone : "",
            'address' => $this->address->address,
            'city' => $this->address->city,
            'state' => $this->address->state,
            'zip' => $this->address->zip,
            'country' => $this->address->country,
        ];
    }
}
