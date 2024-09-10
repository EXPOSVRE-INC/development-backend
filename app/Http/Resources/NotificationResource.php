<?php

namespace App\Http\Resources;

use App\Models\Post;
use App\Models\PostCollection;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'userId' => $this->sender_id,
            'username' => ($this->sender && $this->sender->profile) ? $this->sender->profile->firstName . ' ' . $this->sender->profile->lastName : '',
            'avatar' => ($this->sender) ? $this->sender->getFirstMediaUrl('preview') : '',
            'datetime' => Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->timestamp,
            'deeplink' => $this->deep_link,
            'label' => $this->title,
            'type' => $this->type,
            'image' => ''
        ];

        if ($this->receiverAction != null) {
            $data['receiverAction'] = $this->receiverAction;
        }

        if ($this->type == 'postcomment' || $this->type == 'priceRequest' || $this->type == 'priceRespondedApprove' || $this->type == 'priceRespondedDecline') {
            $post = Post::where(['id' => $this->post_id])->first();
//            $data['image'] = ($this->post != null && $this->post->getFirstMedia('files') && str_contains($this->post->getFirstMedia('files')->mime_type, 'video')) ? $this->post->getFirstMediaUrl('files', 'original') : $this->post->getFirstMediaUrl('files');
            if($this->post != null && $post->getFirstMedia('files') && str_contains($post->getFirstMedia('files')->mime_type, 'video')) {
                $data['image'] = $post != null ? $post->getFirstMediaUrl('files', 'original') : '';
            } else {
                $data['image'] = $post != null ? $post->getFirstMediaUrl('files') : '';
            }
        } else if ($this->type == 'collectioncomment') {
            $collection = PostCollection::where(['id' => $this->post_id])->first();
            $data['image'] = $collection->getFirstMediaUrl('files');
        } else if ($this->type == 'subscription') {
            if ($this->user->subscriptions->contains($this->sender)) {
                $data['type'] = 'followed';
            }
            $data['image'] = '';
        } else if ($this->type == 'like') {
            $post = Post::where(['id' => $this->post_id])->first();
            if ($post != null) {
                $data['image'] = $post->getFirstMediaUrl('files');
            }
        }

        return $data;
    }
}
