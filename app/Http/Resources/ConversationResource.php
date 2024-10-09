<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
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
            'sender' => [
                'id' => $this->senderUser->id,
                'first_name' => $this->senderUser->profile->firstName ?? '',
                'last_name' => $this->senderUser->profile->lastName ?? '',
            ],
            'receiver' => [
                'id' => $this->receiverUser->id,
                'first_name' => $this->receiverUser->profile->firstName ?? '',
                'last_name' => $this->receiverUser->profile->lastName ?? '',
            ],
            'chats' => $this->whenLoaded('chats'), // Load related chats
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
