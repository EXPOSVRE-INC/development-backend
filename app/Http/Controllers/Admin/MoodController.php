<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Mood;
use Illuminate\Http\Request;

class MoodController extends Controller
{
    public function getMoods() {
        $moods = Mood::latest()->get();
        return view('admin.moods.index', [
            'moods' => $moods
        ]);
    }

    public function createMoodForm() {

        return view('admin.moods.create');
    }

    public function createMood(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:moods,name',
        ]);

        Mood::create([
            'name' => $request->input('name'),
        ]);
        return redirect()->route('mood-index')->with('success', 'Mood created successfully!');
    }

    public function editMoodForm($mood_id) {
        $mood = Mood::where(['id' => $mood_id])->first();

        return view('admin.moods.edit', ['mood' => $mood]);
    }

    public function editMood($mood_id, Request $request) {
        $mood = Mood::where(['id' => $mood_id])->first();
        $mood->update([
            'name' => $request->get('name'),
        ]);

        return redirect()->route('mood-index');
    }

    public function deleteMood($mood_id) {
        $mood = Mood::where(['id' => $mood_id])->first();
        $mood->delete();
        return redirect()->route('mood-index');
    }
}
