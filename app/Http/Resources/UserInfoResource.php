<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class UserInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $interests = $this->interestAssignments ? $this->interestAssignments->map(function ($interestRel) {
            return $interestRel->interest->name;
        })->implode(',') : '';

        $notInterests = $this->notInterestAssignments ? $this->notInterestAssignments->map(function ($interestRel) {
            return $interestRel->interest->name;
        })->implode(',') : '';

        return [
            'username' => $this->username,
            'email' => $this->email,
            'profile' => UserProfileResource::make($this->profile),
            'avatar' => $this->getFirstMediaUrl('preview'),
            'tags' => TagsResource::collection($this->tags),
//            'interests' => InterestsResource::collection($this->interestAssignments),
            'interests' => $interests,
//            'not-interests' => InterestsResource::collection($this->notInterestAssignments),
            'notInterests' => $notInterests,
            'subscribed' => auth('api')->user()->isSubscriber($this->id),
            'subscribing' => auth('api')->user()->isSubscription($this->id),
            'marketLikesCount' => $this->profile ? $this->profile->likers()->count() : 0,
            'isMarketLikeByUser' => $this->profile ? $this->profile->isLikedBy(auth('api')->user()) : false,
            'posts' => $this->posts()->count(),
            'followers' => $this->subscribers()->count(),
            'followed' => $this->subscriptions()->count(),
            'verify' => (bool) $this->verify,
            'followersHidden' => $this->setting ? (bool) $this->setting->followersHidden : false,
            'followedHidden' => $this->setting ? (bool) $this->setting->followedHidden : false,
        ];
    }
}
