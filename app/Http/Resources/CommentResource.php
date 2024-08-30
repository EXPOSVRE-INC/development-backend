<?php

namespace App\Http\Resources;

use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = User::where(['id' => $this->user_id])->first();
        $array = [
            'id' => $this->id,
            'message' => $this->comment,
            'userAvatar' => $user->getFirstMediaUrl('preview'),
            'username' => $user->username,
            'createdAt' => Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->timestamp,
        ];
        $reports = Report::where(['model' => 'comment', 'model_id' => $this->id])->get();

        if ($reports->count() > 0) {
            $array['removed'] = true;
        }

        return $array;
    }
}
