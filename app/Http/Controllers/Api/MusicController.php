<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GenreResource;
use App\Http\Resources\MoodResource;
use App\Http\Resources\SongResource;
use App\Models\Genre;
use App\Models\Mood;
use App\Models\Song;
use Illuminate\Http\Request;

class MusicController extends Controller
{
    public function getGenres()
    {
        $genres = Genre::latest()->get();

        if ($genres->isNotEmpty()) {
            return GenreResource::collection($genres);
        }
        return response()->json(['data' => []], 404);
    }

    public function getMoods()
    {
        $moods = Mood::latest()->get();

        if ($moods->isNotEmpty()) {
            return GenreResource::collection($moods);
        }
        return response()->json(['data' => []], 404);
    }

    public function songList(Request $request)
    {
        $songQuery = Song::query()->where('status', 'active');

        if ($request->has('genre_id')) {
            $songQuery->where('genre_id', $request->genre_id);
        }
        if ($request->has('mood_id')) {
            $songQuery->where('mood_id', $request->mood_id);
        }

        $songs = $songQuery->limit(100)->get();
        if ($songs->isEmpty()) {
            return response()->json(['data' => []], 404);
        }
        return SongResource::collection($songs);
    }

    public function songDetail($id)
    {
        $song = Song::where('id', $id)->first();
        if ($song) {
            return new SongResource($song);
        }
        return response()->json(['data' => []], 404);
    }

    public function likeSong($id)
    {
        $song = Song::findOrFail($id);
        $user = auth('api')->user();

        $user->like($song);

        $song->touch();
        return response()->json(['data' => ['loves' => $song->likers()->count()]]);
    }
    public function unlikeSong($id)
    {
        $song = Song::findOrFail($id);
        $user = auth('api')->user();
        $user->unlike($song);
        $song->refresh();

        return response()->json(['data' => ['loves' => $song->likers()->count()]]);
    }

    public function favoriteSong(Song $song)
    {
        $user = auth('api')->user();

        $user->favorite($song);
        $song->touch();

        return response()->json(['favorites' => $song->favoriters()->count()]);
    }

    public function unfavoriteSong(Song $song)
    {
        $user = auth('api')->user();
        $user->unfavorite($song);
        $song->touch();

        return response()->json(['favorites' => $song->favoriters()->count()]);
    }

    public function viewSong($id) {

        $song = Song::where(['id' => $id])->first();

        $song->views_count = $song->views_count + 1;
        $song->views_by_last_day = $song->views_by_last_day + 1;
        $song->save();

        return response()->json(['data' => new songResource($song)]);
    }

}
