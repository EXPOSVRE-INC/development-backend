<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountVerificationResource extends JsonResource
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
            'nickname' => $this->username,
            'fullName' => $this->profile->firstName . ' ' . $this->profile->lastName,
            'accountStatus' => 'flagged',
            'reportsCount' => $this->reports->count(),
            'reason' => $this->reports->first()->reason
        ];
    }
}
