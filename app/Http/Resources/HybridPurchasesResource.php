<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class HybridPurchasesResource extends JsonResource
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
            'postId' => $this->post->id,
            'userId' => $this->seller->id,
            'userName' => $this->seller->username,
            'userAvatar' => $this->seller->getFirstMediaUrl('preview'),
            'status' => (int) $this->status,
            'price' => $this->price,
            'date' => Carbon::createFromFormat('Y-m-d H:i:s', $this->updated_at)->timestamp,
            'type' => 0,
            'currency' => $this->post->currency,
            'shipping' => ($this->shippingMethod != null) ? $this->shippingMethod : '',
            'number' => ($this->trackingNumber != null) ? $this->trackingNumber : '',
            'post' => new PostResource($this->post),
        ];
    }
}
