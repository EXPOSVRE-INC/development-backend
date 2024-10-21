<?php

namespace App\Http\Resources;

use App\Http\Resources\UserInfoResource;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $array = [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'from' => $this->from,
            'to' => $this->to,
            // 'sender' => new UserInfoResource($this->sender),
            // 'receiver' => new UserInfoResource($this->receiver),
            'message' => $this->message,
            'received' => (bool) $this->received,
            'removed' => (bool) $this->removed,
            'datetime' => $this->datetime,
            'read' => (bool) $this->read,
            'message_id' => $this->message_id,
        ];

        if ($this->payload !== null) {
            $array['payload'] = json_decode($this->payload, true);
        }

        return $array;
    }
}
