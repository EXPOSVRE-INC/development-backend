<?php

namespace App\Http\Resources;
use App\Http\Resources\UserInfoResource;
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
            'sender' => new UserInfoResource($this->senderUser),
            'receiver' => new UserInfoResource($this->receiverUser),
            'unread_count' => $this->unread_count,
            'chat' => $this->whenLoaded('chat', function () {
                return [
                    'id' => $this->chat->id,
                    'conversation_id' => $this->chat->conversation_id,
                    'from' => $this->chat->from,
                    'to' => $this->chat->to,
                    'message' => $this->chat->message,
                    'received' => (bool) $this->chat->received,
                    'removed' => (bool) $this->chat->removed,
                    'datetime' => $this->chat->datetime,
                    'read' => (bool) $this->chat->read,
                    'message_id' => $this->chat->message_id,
                ];
            }),
        ];
    }
}
