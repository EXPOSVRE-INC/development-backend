<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\InterestsCategory;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller {

    public function dashboard() {
        return view('adminlte::dashboard');
    }

    public function getAdForm()
    {
        $users = User::with('profile')->get();
        $categories = InterestsCategory::all();
        return view('admin.ads.create', [
            'users' => $users,
            'categories' => $categories
        ]);
    }

    public function postAdForm(Request $request) {
        $request->merge([
            'ad' => 1,
            'allow_views' => 1,
            'allow_to_comment' => 1,
            'shippingIncluded' => 0
        ]);
        $input = $request->all();

        $post = Post::create($input);

        $post->addMediaFromRequest('file')->toMediaCollection('files');

        dump(new PostResource($post));
    }
}
