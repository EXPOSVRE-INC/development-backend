<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SongResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
    $array = [
        'id' => $this->id,
        'title' => $this->title,
        'description' => $this->description,
        'artist_id'=> $this->artist_id,
        'artist'=> new ArtistResource($this->artist),
        'image_file' => $this->image_file,
        'likes_count' =>  (int) $this->likers()->count(),
        'views_count' => $this->views_count,
        'status' => $this->status,
        'full_song_file' => $this->full_song_file,
        'clip_15_sec' => $this->clip_15_sec,
        'favorited' => (bool) $this->hasBeenFavoritedBy(
            auth('api')->user()
        ),
        'liked' => (bool) $this->isLikedBy(auth('api')->user()),
        'viewsByLastDay' => $this->views_by_last_day,
    ];

    if ($this->genre) {
        $array['genre_id'] = $this->genre_id;
        $array['genre'] = new GenreResource($this->genre);
    }

    if ($this->mood) {
        $array['mood_id'] = $this->mood_id;
        $array['mood'] = new MoodResource($this->mood);
    }

    return $array;

    }
}
