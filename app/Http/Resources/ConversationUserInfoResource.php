<?php

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationUserInfoResource extends JsonResource
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

        $isBlockedByAuthUser = $authUser->hasBlocked($this->id);

        return [
            'username' => $this->username,
            'email' => $this->email,
            'profile' => UserProfileResource::make($this->profile),
            'avatar' => $this->getFirstMediaUrl('preview'),
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
