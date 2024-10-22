<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Genre;
use App\Models\Mood;
use Illuminate\Support\Facades\Validator;
use App\Models\Song;
use Illuminate\Http\Request;
use FFMpeg\FFMpeg;

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

        $songs = Song::where('status', 'active')->latest()->get();
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
            'genre_id' => 'required|exists:genre,id',
            'mood_id' => 'required|exists:moods,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image_file' => 'required|file|max:10240',
            'full_song_file' => 'required|file|max:10240',
            'clip_15_sec' => 'required|file|max:10240',
        ]);

        $imagePath = null;
        $songPath = null;
        $clipPath = null;

        if ($request->hasFile('image_file')) {
            $imagePath = $request
                ->file('image_file')
                ->store('uploads/images', 'public');
        }

        if ($request->hasFile('full_song_file')) {
            $songPath = $request
                ->file('full_song_file')
                ->store('uploads/songs', 'public');
        }

        if ($request->hasFile('clip_15_sec')) {
            $clipPath = $request
                ->file('clip_15_sec')
                ->store('uploads/clips', 'public');
        }
        $songDuration = $this->calculateSongDuration(storage_path('app/public/' . $songPath));

        $song = new Song();
        $song->artist_id = $request->artist_id;
        $song->genre_id = $request->genre_id;
        $song->mood_id = $request->mood_id;
        $song->title = $request->title;
        $song->likes_count = 0;
        $song->listens_count = 0;
        $song->song_length = $songDuration;
        $song->description = $request->description;
        $song->image_file = $imagePath;
        $song->full_song_file = $songPath;
        $song->clip_15_sec = $clipPath;

        $song->save();

        // echo $song;
        return redirect()->route('song-index');
    }

    public function editSongForm($song_id) {
        $song = Song::where(['id' => $song_id])->first();
        $artists = Artist::where('status', 'active')->latest()->get();
        $genres = Genre::latest()->get();
        $moods = Mood::latest()->get();

        return view('admin.songs.edit', ['song' => $song , 'artists' => $artists , 'genres' => $genres , 'moods' => $moods]);
    }

    public function edit($song_id, Request $request) {
        $song = Song::where('id', $song_id)->first();

        $song->update([
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'artist_id' => $request->get('artist_id'),
            'genre_id' => $request->get('genre_id'),
            'mood_id' => $request->get('mood_id'),
        ]);

        if ($request->hasFile('image_file')) {
            $imagePath = $request->file('image_file')->store('uploads/images', 'public');
            $song->image = $imagePath;
        }

        if ($request->hasFile('full_song_file')) {
            $songPath = $request->file('full_song_file')->store('uploads/songs', 'public');
            $song->full_song_file = $songPath;
        }

        if ($request->hasFile('clip_15_sec')) {
            $clipPath = $request->file('clip_15_sec')->store('uploads/clips', 'public');
            $song->clip_15_sec = $clipPath;
        }

        $song->save();

        return redirect()->route('song-index')->with('success', 'Song updated successfully');
    }


    public function delete($song_id) {
        $song = Song::where(['id' => $song_id])->first();
        if ($song) {
            $song->status = 'inactive';
            $song->save();
        }
        return redirect()->route('song-index');
    }
}
