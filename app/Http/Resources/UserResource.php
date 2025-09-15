<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $credentials = request(['email', 'password']);
        $token = auth('api')->attempt($credentials);
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'bearer' => $token,
            'profile' => UserProfileResource::make($this->profile),
            'avatar' => $this->getFirstMediaUrl('preview'),
            'isConfirmed' => $this->isConfirmed,
            'marketLikesCount' => $this->profile ? $this->profile->likers()->count() : 0,
            'isMarketLikeByUser' => $this->profile ? $this->profile->isLikedBy(auth('api')->user()) : 0,
            'posts' => $this->posts()
                ->whereDoesntHave('reports', function ($q) {
                    $q->where('model', 'post');
                })
                ->where(function ($query) {
                    $query->whereNull('status')
                        ->orWhere('status', '!=', 'archive');
                })
                ->count(),
            'status' => $this->status,
            'subscribed' => auth('api')->user()->isSubscriber($this->id),
            'subscribing' => auth('api')->user()->isSubscription($this->id),
            'verify' => (bool) $this->verify,
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
            'followersHidden' => $this->setting ? (bool) $this->setting->followersHidden : false,
            'followedHidden' => $this->setting ? (bool) $this->setting->followedHidden : false,
        ];
    }
}
