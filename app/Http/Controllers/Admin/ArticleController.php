<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\TagsResource;
use App\Models\InterestsCategory;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function scheduled() {
        $now = Carbon::now()->toDateTimeString();
        $posts = Post::where('link', '<>', 'NULL')
            ->where(['ad' => 1])
            ->where(['isArticle' => 1])
            ->where(['owner_id' => 1])
            ->where(['status' => null])
            ->where('publish_date', '>', $now)->get();

        return view('admin.articles.index', [
            'posts' => $posts
        ]);
    }


    public function published() {
        $now = Carbon::now()->toDateTimeString();
        $posts = Post::where('link', '<>', 'NULL')
            ->where(['ad' => 1])
            ->where(['isArticle' => 1])
            ->where(['owner_id' => 1])
            ->where(['status' => null])
            ->where('publish_date', '<', $now)->get();

        return view('admin.articles.index', [
            'posts' => $posts
        ]);
    }

    public function drafts() {
        $posts = Post::where(['status' => 'draft'])->get();
        return view('admin.articles.index', [
            'posts' => $posts
        ]);
    }

    public function archive() {
        $posts = Post::where(['status' => 'archive'])
            ->where('link', '<>', 'NULL')
            ->where(['ad' => 1])
            ->where(['isArticle' => 1])
            ->where(['owner_id' => 1])
            ->get();
        return view('admin.articles.index', [
            'posts' => $posts
        ]);
    }

    public function moveToArchive($id) {
        $post = Post::where(['id' => $id])->first();
        $post->status = 'archive';
        $post->is_archived = 1;
        $post->save();
        return redirect()->route('articles-archive');
    }

    public function moveFromArchive($id) {
        $post = Post::where(['id' => $id])->first();
        $post->status = null;
        $post->is_archived = 0;
        $post->save();
        return redirect()->route('articles-published');
    }

    public function getAdForm()
    {
        $users = User::with('profile')->get();
        $categories = InterestsCategory::all();
        $tags = Tag::all();
        return view('admin.articles.create', [
            'users' => $users,
            'categories' => $categories,
            'tags' => TagsResource::collection($tags)
        ]);
    }

    public function editForm($id)
    {
        $post = Post::where(['id' => $id])->first();
        $users = User::with('profile')->get();
        $categories = InterestsCategory::all();
        $tags = Tag::all();
        return view('admin.articles.edit', [
            'post' => $post,
            'users' => $users,
            'categories' => $categories,
            'tags' => TagsResource::collection($tags)
        ]);
    }

    public function editFormPost($id, Request $request) {
        if ($request->has('publish_date')) {
            $request->merge(['publish_date' => Carbon::createFromFormat('d/m/Y H:i', $request->get('publish_date'))]);
        }

        $post = Post::where(['id' => $id])->first();

        $input = $request->all();

        $post->update($input);

        if ($request->has('file')) {
            $media = $post->getFirstMedia('files');
            if ($media != null) {
                $media->delete();
            }
            $post->addMediaFromRequest('file')->toMediaCollection('files');
        }

        return redirect()->route('articles-published');

    }

    public function postAdForm(Request $request) {
        $request->merge([
            'ad' => 1,
            'owner_id' => 1,
            'shippingIncluded' => 0,
            'isArticle' => 1,
            'publish_date' => Carbon::createFromFormat('d/m/Y H:i', $request->get('publish_date'))
        ]);
        $input = $request->all();

        $post = Post::create($input);

        if ($request->has('interest') && $request->get('interest') != null) {
            $post->assignInterest($request->get('interest'));
        }

        $post->addMediaFromRequest('file')->toMediaCollection('files');

        return redirect()->route('articles-scheduled');
    }
}
