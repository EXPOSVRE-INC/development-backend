<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserMilestoneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $likesCount = 0;
        foreach ($this->posts as $post) {
            $postCount = $post->likers()->count();
            $likesCount = $likesCount+$postCount;
        }
        return [
//            'createdFirstNft' => [
//                'current' => 0,
//                'required' => 5
//            ],
            [
                'name' => 'madeFirstPost',
                'current' => $this->posts()->count() > 0 ? 1 : 0,
                'required' => 1,
            ],
            [
                'name' => 'followed10Accounts',
                'current' => $this->subscriptions()->count(),
                'required' => 10,
            ],
            [
                'name' => 'recived10Followers',
                'current' => $this->subscribers()->count(),
                'required' => 10,
            ],
            [
                'name' => 'createdACollectionPage',
                'current' => 1,
                'required' => 5,
            ],
//            'created10Nfts' => [
//                'current' => 0,
//                'required' => 5
//            ],
            [
                'name' => 'made50Posts',
                'current' => $this->posts()->count(),
                'required' => 50,
            ],
            [
                'name' => 'followed100Accounts',
                'current' => $this->subscriptions()->count(),
                'required' => 100,
            ],
            [
                'name' => 'recived100Followers',
                'current' => $this->subscribers()->count(),
                'required' => 100,
            ],
            [
                'name' => 'gave100Crowns',
                'current' => $likesCount,
                'required' => 100,
            ]
        ];
    }
}
