<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\GenreResource;
use App\Http\Resources\MoodResource;
use App\Models\Genre;
use App\Models\Mood;
use Illuminate\Http\Request;

class MusicController extends Controller
{
    public function getGenres() {
        $genres = Genre::latest()->get();
        return GenreResource::collection($genres);
    }

    public function getMoods() {
        $moods = Mood::latest()->get();
        return MoodResource::collection($moods);

    }
}
