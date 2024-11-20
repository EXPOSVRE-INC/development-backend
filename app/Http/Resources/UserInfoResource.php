<?php

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Builder;
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

        $authUser = auth('api')->user();

        $interests = $this->interestAssignments ? $this->interestAssignments->map(function ($interestRel) {
            return $interestRel->interest->name;
        })->implode(',') : '';

        $notInterests = $this->notInterestAssignments ? $this->notInterestAssignments->map(function ($interestRel) {
            return $interestRel->interest->name;
        })->implode(',') : '';

        $isBlockedByAuthUser = $authUser->hasBlocked($this->id);

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
            'posts' => $this->posts()->get()->filter(function ($post) {
                return $post->reports->count() == 0;})->count(),
            // 'followers' => $this->subscribers()->count(),
            // 'followed' => $this->subscriptions()->count(),

            'followers' => $this->subscribers()
                ->whereHas('profile')
                ->whereDoesntHave('blockedBy', function (Builder $query) {
                    $query->where('user_id', $this->id);
                })
                ->whereDoesntHave('blocks', function (Builder $query) {
                    $query->where('blocking_id', $this->id);
                })
                ->count(),
            'followed' => $this->subscriptions()
                ->whereHas('profile')
                ->whereDoesntHave('blockedBy', function (Builder $query) {
                    $query->where('user_id', $this->id);
                })
                ->whereDoesntHave('blocks', function (Builder $query) {
                    $query->where('blocking_id', $this->id);
                })
                ->count(),
            'verify' => (bool) $this->verify,
            'followersHidden' => $this->setting ? (bool) $this->setting->followersHidden : false,
            'followedHidden' => $this->setting ? (bool) $this->setting->followedHidden : false,
            'isBlocked' => $isBlockedByAuthUser,
        ];
    }
}
