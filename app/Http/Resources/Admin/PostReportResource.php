<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class PostReportResource extends JsonResource
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
            'nickname' => $this->owner->username,
            'fullName' => $this->owner->profile->firstName . ' ' . $this->owner->profile->lastName,
            'accountStatus' => 'flagged',
            'reportsCount' => $this->reports->count(),
            'reason' => $this->reports->first()->reason
        ];

        if ($this->getFirstMedia('files') && str_contains($this->getFirstMedia('files')->mime_type, 'image')) {
            $data['image'] = $this->getFirstMediaUrl('files');
        } else if ($this->getFirstMedia('files') && str_contains($this->getFirstMedia('files')->mime_type, 'video')) {
            $data['image'] = $this->getFirstMediaUrl('files', 'original');
        }

        return $data;
    }
}
