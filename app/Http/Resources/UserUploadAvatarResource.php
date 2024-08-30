<?php

namespace App\Http\Resources;

use App\Models\UserProfile;
use Illuminate\Http\Resources\Json\JsonResource;

class UserUploadAvatarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if ($this->profile == null) {
            $newProfile = new UserProfile();
//            $newProfile->id = 0;
            $newProfile->user_id = $this->id;
            $newProfile->firstName = "";
            $newProfile->lastName = "";
            $newProfile->phone = "";
            $newProfile->jobTitle = "";
            $newProfile->jobDescription = "";
            $this->profile = $newProfile;
            $newProfile->save();
        }
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'profile' => UserProfileResource::make($this->profile),
            'avatar' => $this->getFirstMediaUrl('preview'),
            'isConfirmed' => $this->isConfirmed,
            'marketLikesCount' => $this->profile ? $this->profile->likers()->count() : 0,
            'isMarketLikeByUser' => $this->profile ? $this->profile->isLikedBy(auth('api')->user()) : 0,
            'posts' => $this->posts()->count(),
            'followers' => $this->subscribers()->count(),
            'followed' => $this->subscriptions()->count(),
            'followersHidden' => $this->setting ? (bool) $this->setting->followersHidden : false,
            'followedHidden' => $this->setting ? (bool) $this->setting->followedHidden : false,
        ];
    }
}
