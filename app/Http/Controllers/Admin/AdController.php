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

class AdController extends Controller
{
    public function scheduled()
    {
        $now = Carbon::now()->setTimezone('US/Eastern')->toDateTimeString();
        //        dump($now);
        $posts = Post::where('publish_date', '>', $now)
            ->where(['owner_id' => 1])
            ->where(['status' => null])
            ->where(['ad' => 1])->get();

        return view('admin.ads.index', [
            'posts' => $posts
        ]);
    }


    public function published()
    {
        $now = Carbon::now()->setTimezone('US/Eastern')->toDateTimeString();

        $posts = Post::where('owner_id', 1)
            ->whereDoesntHave('reports')
            ->where(function ($query) {
                $query->where('status', '!=', 'archive')
                    ->orWhereNull('status')
                    ->orWhere('status', '');
            })
            ->where('publish_date', '<', $now)
            ->where('ad', 1)
            ->orderByRaw("COALESCE(publish_date, created_at) DESC")
            ->get();

        return view('admin.ads.index', [
            'posts' => $posts
        ]);
    }

    public function drafts()
    {
        $posts = Post::where(['owner_id' => 1])
            ->where(['status' => 'draft'])
            ->where(['ad' => 1])
            ->orderBy('id', 'DESC')
            ->get();
        return view('admin.ads.index', [
            'posts' => $posts
        ]);
    }

    public function archive()
    {
        $posts = Post::where(['owner_id' => 1])
            ->where(['status' => 'archive'])
            ->where(['ad' => 1])
            ->orderBy('id', 'DESC')
            ->get();
        return view('admin.ads.index', [
            'posts' => $posts
        ]);
    }

    public function highestPriority($id)
    {

        $now = Carbon::now()->setTimezone('US/Eastern')->toDateTimeString();

        $posts = Post::where('link', '<>', 'NULL')
            ->where(['owner_id' => 1])
            ->where(['status' => null])
            ->where('publish_date', '<', $now)->where(['ad' => 1])
            ->orderBy('order_priority', 'ASC')
            ->get();
        //        dd($posts);
        $key = 2;

        foreach ($posts as $post) {
            if ($post->id != $id) {
                $post->order_priority = $key;
                $post->save();
                $key++;
            }
        }

        $post = Post::where(['id' => $id])->first();
        $post->order_priority = 1;
        $post->save();

        return redirect()->route('ads-published');
    }

    public function moveToArchive($id)
    {
        $post = Post::where(['id' => $id])->first();
        $post->status = 'archive';
        $post->is_archived = 1;
        $post->save();
        return redirect()->route('ads-archive');
    }

    public function moveFromArchive($id)
    {
        $post = Post::where(['id' => $id])->first();
        $post->status = null;
        $post->is_archived = 0;
        $post->save();
        return redirect()->route('ads-published');
    }

    public function getAdForm()
    {
        $users = User::with('profile')->get();
        $categories = InterestsCategory::orderBy('slug')->get()->toTree();
        //        dump($categories);
        $tags = Tag::all();
        return view('admin.ads.create', [
            'users' => $users,
            'categories' => $categories,
            //            'tags' => TagsResource::collection($tags)
        ]);
    }

    public function editAddForm($id)
    {
        $post = Post::where(['id' => $id])->first();
        //        dump($post->interests);
        $categories = InterestsCategory::all();
        return view('admin.ads.edit', [
            'post' => $post,
            'categories' => $categories
        ]);
    }

    public function editAddFormPost($id, Request $request)
    {
        if ($request->has('publish_date')) {
            $request->merge(['publish_date' => Carbon::createFromFormat('d/m/Y H:i', $request->get('publish_date'), 'US/Eastern')]);
        }


        $post = Post::where(['id' => $id])->first();

        $input = $request->all();

        //        dd($input);

        $post->update($input);

        if ($request->has('interest') && $request->get('interest') != null) {
            $post->assignInterest($request->get('interest'));
        }

        if ($request->has('file')) {
            $media = $post->getMedia('files');
            foreach ($media as $file) {
                if ($file != null) {
                    $file->delete();
                }
            }
            //            dd($request->file('file'));
            //            foreach ($request->get('file[]') as $file) {
            $post->addMultipleMediaFromRequest(['file'])
                ->each(function ($fileAdder) {
                    $fileAdder->toMediaCollection('files');
                });
            //                    ->toMediaCollection('files');
            //            }
        }

        $headerType = $request->get('header_type');

        if ($request->get('remove_header_video')) {
            $post->clearMediaCollection('header_video');
        }

        if ($request->get('remove_thumb')) {
            $post->clearMediaCollection('thumb');
        }

        if ($headerType === 'video') {
            $post->clearMediaCollection('thumb');

            if ($request->hasFile('header_video')) {
                $post->clearMediaCollection('header_video'); // optional: only if not already cleared
                $post->addMediaFromRequest('header_video')->toMediaCollection('header_video');
            }
        }

        if ($headerType === 'image') {
            $post->clearMediaCollection('header_video');

            if ($request->hasFile('thumbnail')) {
                $post->clearMediaCollection('thumb'); // optional
                $post->addMediaFromRequest('thumbnail')->toMediaCollection('thumb');
            }
        }

        if ($request->has('video_thumbnail')) {
            $media = $post->getFirstMedia('video_thumbnail');
            if ($media != null) {
                $media->delete();
            }
            $post->addMediaFromRequest('video_thumbnail')->toMediaCollection('video_thumb');
        }

        if ($request->has('video')) {
            $media = $post->getFirstMedia('video');
            if ($media != null) {
                $media->delete();
            }
            $post->addMediaFromRequest('video')->toMediaCollection('video');
        }

        return redirect()->route('ads-published');
    }

    public function postAdForm(Request $request)
    {
        $request->validate([
            'thumbnail' => 'nullable|image', // Max 5MB
            'header_video' => 'nullable|mimetypes:video/mp4,video/avi,video/mov|max:10240', // Max 10MB
        ]);

        if ($request->hasFile('thumbnail') && $request->hasFile('header_video')) {
            return back()->withErrors(['Only one of header image or video can be uploaded.']);
        }

        $request->merge([
            'link' => ($request->get('link') != null) ? $request->get('link') : '',
            'ad' => 1,
            'allow_views' => 1,
            'allow_to_comment' => 1,
            'shippingIncluded' => 0,
            'publish_date' => Carbon::createFromFormat('d/m/Y H:i', $request->get('publish_date')),
            'owner_id' => 1
        ]);
        $input = $request->all();

        $post = Post::create($input);

        if ($request->has('interests') && $request->get('interests') != null) {
            foreach ($request->get('interests') as $interest) {
                $post->assignInterest($interest);
            }
        }

        if ($request->hasFile('file')) {
            $post->addMultipleMediaFromRequest(['file'])
                ->each(function ($fileAdder) {
                    $fileAdder->toMediaCollection('files');
                });
        }
        if ($request->hasFile('thumbnail')) {
            $post->addMediaFromRequest('thumbnail')->toMediaCollection('thumb');
        } elseif ($request->hasFile('header_video')) {
            $post->addMediaFromRequest('header_video')->toMediaCollection('header_video');
        }

        if ($request->hasFile('video')) {
            $post->addMediaFromRequest('video')->toMediaCollection('video');
        }

        if ($request->hasFile('video_thumbnail')) {
            $post->addMediaFromRequest('video_thumbnail')->toMediaCollection('video_thumb');
        }

        return redirect()->route('ads-scheduled');
    }

    public function deletePost($id)
    {
        $post = Post::where(['id' => $id])->first();
        $post->delete();

        return redirect()->back();
    }
}
