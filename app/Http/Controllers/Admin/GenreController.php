<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function getGenre() {
        $genres = Genre::latest()->get();
        return view('admin.genres.genre', [
            'genres' => $genres
        ]);
    }

    public function createGenreForm() {

        return view('admin.genres.create-genre');
    }

    public function createGenre(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:genre,name',
        ]);

        Genre::create([
            'name' => $request->input('name'),
        ]);
        return redirect()->route('genre-index')->with('success', 'Genre created successfully!');
    }

    public function editGenreForm($genre_id) {
        $genre = Genre::where(['id' => $genre_id])->first();

        return view('admin.genres.edit', ['genre' => $genre]);
    }

    public function editGenre($genre_id, Request $request) {
        $genre = Genre::where(['id' => $genre_id])->first();
        $genre->update([
            'name' => $request->get('name'),
        ]);

        return redirect()->route('genre-index');
    }

    public function deleteGenre($genre_id) {
        $genre = Genre::where(['id' => $genre_id])->first();
        $genre->delete();
        return redirect()->route('genre-index');
    }
}
