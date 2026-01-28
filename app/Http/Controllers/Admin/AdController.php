<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
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
            ->orderBy('order_priority', 'ASC')
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
        DB::transaction(function () use ($id) {

            $post = Post::findOrFail($id);

            if (
                !$post->isArticle ||
                $post->is_archived ||
                is_null($post->publish_date)
            ) {
                return;
            }

            $oldPriority = $post->order_priority;

            if ($oldPriority === 1) {
                return;
            }

            Post::where('ad', 1)
                ->whereNotNull('publish_date')
                ->where('is_archived', 0)
                ->where('order_priority', '<', $oldPriority)
                ->increment('order_priority');

            $post->order_priority = 1;
            $post->save();
        });

        return redirect()->route('ads-published');
    }

    public function moveToArchive($id)
    {
        $post = Post::where(['id' => $id])->first();
        DB::transaction(function () use ($post) {

            $oldPriority = $post->order_priority;
            $post->update([
                'status' => 'archive',
                'is_archived' => true,
                'order_priority' => null,
            ]);

            if ($oldPriority) {
                Post::where('ad', 1)
                    ->whereNotNull('publish_date')
                    ->where('is_archived', 0)
                    ->where('order_priority', '>', $oldPriority)
                    ->decrement('order_priority');
            }
        });
        return redirect()->route('ads-archive');
    }

    public function moveFromArchive($id)
    {
        $post = Post::where(['id' => $id])->first();

        $post->update([
            'is_archived' => false,
            'status' => null,
            'order_priority' => null,
        ]);
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


        DB::transaction(function () use ($id, $request) {

            $post = Post::findOrFail($id);

            $oldPriority   = $post->order_priority;
            $wasPublished  = !is_null($post->publish_date);

            $input = $request->except('order_priority');

            $post->update($input);

            $isPublished = !is_null($post->publish_date);

            if (!$wasPublished && $isPublished && $post->ad && !$post->is_archived) {

                $newPriority = $request->get('order_priority');

                if ($newPriority) {
                    Post::where('isArticle', 1)
                        ->whereNotNull('publish_date')
                        ->where('is_archived', 0)
                        ->where('order_priority', '>=', $newPriority)
                        ->increment('order_priority');

                    $post->order_priority = $newPriority;
                } else {
                    Post::where('isArticle', 1)
                        ->whereNotNull('publish_date')
                        ->where('is_archived', 0)
                        ->increment('order_priority');

                    $post->order_priority = 1;
                }

                $post->save();
            }

            if ($wasPublished && $isPublished && !$post->is_archived) {

                $newPriority = $request->get('order_priority');

                if ($newPriority && $newPriority != $oldPriority) {

                    if ($newPriority < $oldPriority) {
                        // Move UP
                        Post::where('ad', 1)
                            ->whereNotNull('publish_date')
                            ->where('is_archived', 0)
                            ->whereBetween('order_priority', [$newPriority, $oldPriority - 1])
                            ->increment('order_priority');
                    } else {
                        // Move DOWN
                        Post::where('ad', 1)
                            ->whereNotNull('publish_date')
                            ->where('is_archived', 0)
                            ->whereBetween('order_priority', [$oldPriority + 1, $newPriority])
                            ->decrement('order_priority');
                    }

                    $post->order_priority = $newPriority;
                    $post->save();
                }
            }
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
                $post->addMultipleMediaFromRequest(['file'])
                    ->each(function ($fileAdder) {
                        $fileAdder->toMediaCollection('files');
                    });
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
        });

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

        DB::transaction(function () use ($request) {

            $request->merge([
                'link' => $request->get('link') ?? '',
                'ad' => 1,
                'allow_views' => 1,
                'allow_to_comment' => 1,
                'shippingIncluded' => 0,
                'publish_date' => Carbon::createFromFormat('d/m/Y H:i', $request->get('publish_date')),
                'owner_id' => 1
            ]);

            $input = $request->except('order_priority');

            $post = Post::create($input);

            if ($post->ad && $post->publish_date && !$post->is_archived) {
                $newPriority = $request->get('order_priority');

                if ($newPriority) {
                    // Shift posts at and below new priority
                    Post::where('ad', 1)
                        ->whereNotNull('publish_date')
                        ->where('is_archived', 0)
                        ->where('order_priority', '>=', $newPriority)
                        ->increment('order_priority');

                    $post->order_priority = $newPriority;
                } else {
                    // Default → move to top
                    Post::where('ad', 1)
                        ->whereNotNull('publish_date')
                        ->where('is_archived', 0)
                        ->increment('order_priority');

                    $post->order_priority = 1;
                }

                $post->save();
            }

            if ($request->has('interests')) {
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
        });

        return redirect()->route('ads-scheduled');
    }

    public function deletePost($id)
    {
        $post = Post::where(['id' => $id])->first();
        $post->delete();

        return redirect()->back();
    }
}
