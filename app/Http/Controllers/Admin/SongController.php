<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Genre;
use App\Models\Mood;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;
use App\Models\Song;
use Illuminate\Http\Request;
use FFMpeg\FFMpeg;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Log;
use App\Helpers\MediaHelper;


class SongController extends Controller
{
    public function calculateSongDuration($filePath)
    {
        $ffmpeg = FFMpeg::create();

        $audio = $ffmpeg->open($filePath);

        $duration = $audio->getFormat()->get('duration');
        $duration = round($duration);

        // Calculate hours, minutes, and seconds
        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
    public function index()
    {

        $songs = Song::where('status', 'active')
            ->withCount('posts')
            ->get();

        return view('admin.songs.index', [
            'songs' => $songs,
        ]);
    }

    public function createForm()
    {
        $artists = Artist::latest()->get();
        $moods = Mood::latest()->get();
        $genres = Genre::latest()->get();
        return view('admin.songs.create', [
            'artists' => $artists,
            'moods' => $moods,
            'genres' => $genres,
        ]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'artist_id' => 'required|exists:artists,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image_file' => 'required|file|max:10240',
            'full_song_file' => 'required|file',
            'clip_15_sec' => 'required|file',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (is_null($request->genre_id) && is_null($request->mood_id)) {
                $validator->errors()->add('genre_id', 'Either genre_id or mood_id is required.');
                $validator->errors()->add('mood_id', 'Either genre_id or mood_id is required.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $imagePath = null;
        $songPath = null;
        $clipPath = null;

        if ($request->hasFile('image_file')) {
            $originalFileName = $request->file('image_file')->getClientOriginalName();
            $fileNameWithoutSpaces = str_replace(' ', '_', $originalFileName);
            $imagePath = $request->file('image_file')->storeAs('uploads/images', $fileNameWithoutSpaces, 'public');
            $imageUrl = url('storage/' . $imagePath) ?? null;
        }

        if ($request->hasFile('full_song_file')) {
            $originalFileName = $request->file('full_song_file')->getClientOriginalName();
            $fileNameWithoutSpaces = str_replace(' ', '_', $originalFileName);
            $songPath = $request->file('full_song_file')->storeAs('uploads/songs', $fileNameWithoutSpaces, 'public');
            $songUrl = url('storage/' . $songPath) ?? null;
        }

        if ($request->hasFile('clip_30_sec')) {
            $originalFileName = $request->file('clip_30_sec')->getClientOriginalName();
            $fileNameWithoutSpaces = str_replace(' ', '_', $originalFileName);
            $clipPath = $request->file('clip_30_sec')->storeAs('uploads/clips', $fileNameWithoutSpaces, 'public');
            $clipUrl = url('storage/' . $clipPath) ?? null;
        }

        $songDuration = $this->calculateSongDuration(storage_path('app/public/' . $songPath));

        $song = new Song();
        $song->artist_id = $request->artist_id;
        $song->genre_id = $request->genre_id;
        $song->mood_id = $request->mood_id;
        $song->title = $request->title;
        $song->likes_count = 0;
        $song->views_count = 0;
        $song->user_id = auth()->user()->id;
        $song->song_length = $songDuration;
        $song->status = 'active';
        $song->description = $request->description;
        $song->image_file = $imageUrl ?? null;
        $song->full_song_file = $songUrl ?? null;
        $song->clip_15_sec = $clipUrl ?? null;
        $song->views_by_last_day = 0;

        $song->save();

        return redirect()->route('song-index');
    }

    public function editSongForm($song_id)
    {
        $song = Song::where(['id' => $song_id])->first();
        $artists = Artist::where('status', 'active')->latest()->get();
        $genres = Genre::latest()->get();
        $moods = Mood::latest()->get();

        return view('admin.songs.edit', ['song' => $song, 'artists' => $artists, 'genres' => $genres, 'moods' => $moods]);
    }

    public function edit($song_id, Request $request)
    {
        $song = Song::where('id', $song_id)->first();

        $song->update([
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'artist_id' => $request->get('artist_id'),
            'genre_id' => $request->get('genre_id'),
            'mood_id' => $request->get('mood_id'),
        ]);

        if ($request->hasFile('image_file')) {
            $originalFileName = $request->file('image_file')->getClientOriginalName();
            $fileNameWithoutSpaces = str_replace(' ', '_', $originalFileName);
            $imagePath = $request->file('image_file')->storeAs('uploads/images', $fileNameWithoutSpaces, 'public');
            $imageUrl = url('storage/' . $imagePath);
            $song->image_file = $imageUrl;
        }

        if ($request->hasFile('full_song_file')) {
            $originalFileName = $request->file('full_song_file')->getClientOriginalName();
            $fileNameWithoutSpaces = str_replace(' ', '_', $originalFileName);
            $songPath = $request->file('full_song_file')->storeAs('uploads/songs', $fileNameWithoutSpaces, 'public');
            $songUrl = url('storage/' . $songPath);
            $song->full_song_file = $songUrl;
        }

        if ($request->hasFile('clip_30_sec')) {
            $originalFileName = $request->file('clip_30_sec')->getClientOriginalName();
            $fileNameWithoutSpaces = str_replace(' ', '_', $originalFileName);
            $clipPath = $request->file('clip_30_sec')->storeAs('uploads/clips', $fileNameWithoutSpaces, 'public');
            $clipUrl = url('storage/' . $clipPath);
            $song->clip_15_sec = $clipUrl;
        }

        $song->save();

        return redirect()->route('song-index')->with('success', 'Song updated successfully');
    }


    public function delete($song_id)
    {
        $song = Song::where(['id' => $song_id])->first();
        if ($song) {
            // Mark the song as deleted
            $song->update(['status' => 'deleted']);

            $posts = Post::where('song_id', $song->id)->get();

            foreach ($posts as $post) {
                $mediaItems = Media::where('model_id', $post->id)->get();

                foreach ($mediaItems as $media) {
                    $inputPath = $media->getPath();
                    $outputPath = storage_path('app/public/muted_' . basename($inputPath));

                    $result = MediaHelper::muteMediaAudio($inputPath, $outputPath);

                    if (!$result) {
                        Log::error("Failed to mute media: " . $inputPath);
                    }
                }
            }

            return redirect()->route('song-index')->with('success', 'Song deleted and media muted successfully');
        } else {
            return redirect()->route('song-index')->with('error', 'Song not found');
        }
    }
}