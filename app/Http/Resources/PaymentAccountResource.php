<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentAccountResource extends JsonResource
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
            'bankName' => $this->nameOfBank,
            'accountNumber' => $this->accountNumber,
            'city' => ($this->city) ? $this->city : 'New York',
            'routingNumber' => $this->routingNumber ? $this->routingNumber : '123',
            'state' => $this->state ? $this->state : 'AL',
            'zip' => $this->zipCode ? (string) $this->zipCode : '12345',
            'bankAddress' => $this->addressOfBank ? $this->addressOfBank : 'Custom address',
            'isActive' => (bool) $this->isActive
        ];
    }
}
