<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class UserSubscriptionResource extends JsonResource
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
            'interests' => $interests,
            'not-interests' => $notInterests,
//            'interests' => InterestsResource::collection($this->interestAssignments),
//            'not-interests' => InterestsResource::collection($this->notInterestAssignments),
            'marketLikesCount' => $this->profile != null ? $this->profile->likers()->count() : 0,
            'isMarketLikeByUser' => $this->profile != null ? $this->profile->isLikedBy(auth('api')->user()) : false,
            'posts' => $this->posts()->count(),
            'verify' => (bool) $this->verify,
            'subscribed' => auth('api')->user()->isSubscription($this->id),
            'subscribing' => auth('api')->user()->isSubscriber($this->id),
            'followers' => $this->subscribers()->count(),
            'followed' => $this->subscriptions()->count(),
            'followersHidden' => $this->setting ? (bool) $this->setting->followersHidden : false,
            'followedHidden' => $this->setting ? (bool) $this->setting->followedHidden : false,
        ];
    }
}
