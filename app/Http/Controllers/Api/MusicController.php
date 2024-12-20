<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
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
        $genreOrder = [
            'POP', 'HIP HOP', 'RAP', 'COUNTRY', 'R&B',
            'ELECTRONIC/DANCE', 'EDM', 'FOLK', 'ROCK',
            'ALTERNATIVE', 'WORLD'
        ];

        $genres = Genre::whereIn('name', $genreOrder)
            ->orderByRaw('FIELD(name, ' . implode(',', array_map(fn($item) => "'$item'", $genreOrder)) . ')')
            ->get();

        if ($genres->isNotEmpty()) {
            return GenreResource::collection($genres);
        }

        return response()->json(['data' => []], 200);
    }


    public function getMoods()
    {
        $moodOrder = [
            'JOYFUL/ENERGETIC', 'CALMING/RELAXING/CONTEMPLATIVE',
            'SAD/ MELANCHOLIC', 'EPIC/DRAMATIC', 'MYSTERIOUS',
            'ROMANTIC', 'TENSE/ANXIOUS/SCARY', 'UPLIFTING/OPTIMISTIC',
            'PUMPED UP'
        ];

        $moods = Mood::whereIn('name', $moodOrder)
            ->orderByRaw('FIELD(name, ' . implode(',', array_map(fn($item) => "'$item'", $moodOrder)) . ')')
            ->get();

        if ($moods->isNotEmpty()) {
            return MoodResource::collection($moods);
        }
        return response()->json(['data' => []], 200);
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
            return response()->json(['data' => []], 200);
        }
        return SongResource::collection($songs);
    }

    public function songDetail($id)
    {
        $song = Song::where('id', $id)->first();
        if ($song) {
            return new SongResource($song);
        }
        return response()->json(['data' => []], 200);
    }

    public function likeSong($id)
    {
        $song = Song::findOrFail($id);
        if (!$song) {
            return response()->json(['message' => 'Song not found'], 404);
        }
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

        if (!$song) {
            return response()->json(['data' => []], 404);
        }
        $song->views_count = $song->views_count + 1;
        $song->views_by_last_day = $song->views_by_last_day + 1;
        $song->save();

        return response()->json(['data' => new songResource($song)]);
    }

    public function commentSong($id, Request $request)
    {
        try {
            $user = auth('api')->user();
            $song = Song::where(['id' => $id])->first();

            $song->commentAs($user, $request->get('comment'));
            return response()->json(['data' => CommentResource::collection($song->comments)]);

        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }
    public function songListComments($id)
    {
        $song = Song::where(['id' => $id])->first();
        return response()->json(['data' => CommentResource::collection($song->comments)]);
    }

    public function download($id)
    {
        try {
            $song = Song::findOrFail($id);
            $song->increment('download_count');

            $fileName = basename($song->full_song_file);

            $filePath = public_path('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'songs' . DIRECTORY_SEPARATOR . $fileName);

            if (!file_exists($filePath)) {
                return response()->json(['error' => 'File not found'], 404);
            }

            $size = filesize($filePath);

            return response()->file($filePath, [
                'Content-Type' => 'audio/mpeg',
                'Content-Length' => $size,
                'Content-Disposition' => 'attachment; filename="' . $song->title . '.mp3"',
                'Accept-Ranges' => 'bytes',
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Download failed: ' . $e->getMessage()], 500);
        }
    }
}
