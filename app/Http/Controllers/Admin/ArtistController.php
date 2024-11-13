<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\MediaHelper;
use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Post;
use App\Models\Song;
use Closure;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ArtistController extends Controller
{
    public function handle($request, Closure $next)
    {
        if (session()->has('previous_url')) {
            session(['two_steps_back' => session('previous_url')]);
        }

        session(['previous_url' => url()->previous()]);

        return $next($request);
    }
    public function index()
    {
        $artists = Artist::where('status', 'active')->latest()->get();
        return view('admin.artists.index', [
            'artists' => $artists
        ]);
    }

    public function createForm()
    {
        return view('admin.artists.create');
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:artists,name',
            'profile_image' => 'image'
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:artists,name',
            'profile_image' => 'image'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $imagePath = null;
        if ($request->hasFile('profile_image')) {
            $originalFileName = $request->file('profile_image')->getClientOriginalName();
            $fileNameWithoutSpaces = str_replace(' ', '_', $originalFileName);
            $imagePath = $request->file('profile_image')->storeAs('uploads/artist_profile_images', $fileNameWithoutSpaces, 'public');
            $imageUrl = url('storage/' . $imagePath);
        }

        $artist = new Artist();
        $artist->name = $request->input('name');
        $artist->profile_image = $imageUrl;
        $artist->save();


        return redirect()->to($request->input('previous_url'));
    }

    public function editArtistForm($artist_id)
    {
        $artist = Artist::where(['id' => $artist_id])->first();

        return view('admin.artists.edit', ['artist' => $artist]);
    }

    public function editArtist($artist_id, Request $request)
    {
        $artist = Artist::where(['id' => $artist_id])->first();
        $artist->update([
            'name' => $request->get('name'),
        ]);
        if ($request->hasFile('profile_image')) {
            $originalFileName = $request->file('profile_image')->getClientOriginalName();
            $fileNameWithoutSpaces = str_replace(' ', '_', $originalFileName);
            $imagePath = $request->file('profile_image')->storeAs('uploads/artist_profile_images', $fileNameWithoutSpaces, 'public');
            $imageUrl = url('storage/' . $imagePath);
            $artist->profile_image = $imageUrl;
        }
        // $artist->profile_image = $imageUrl;
        $artist->save();
        return redirect()->route('artist-index');
    }


    public function delete($artist_id)
    {
        $artist = Artist::where(['id' => $artist_id])->first();
        if ($artist) {
            $artist->status = 'inactive';
            $artist->save();

            $songs = Song::where('artist_id', $artist_id)->get();
            foreach ($songs as $song) {
                $song->status = 'deleted';
                $song->save();

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
            }
        }

        return redirect()->route('artist-index');
    }
}
