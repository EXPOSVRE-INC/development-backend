<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PostReportResource;
use App\Models\Post;
use App\Models\User;
use App\Notifications\IssueWarningNotification;
use Illuminate\Http\Request;

class PostReportController extends Controller
{
    public function postReports()
    {
        $posts = Post::has('reports')->get();
        return view('admin.reports.post-index', ['posts' => PostReportResource::collection($posts)]);
    }

    public function warnings()
    {
        $posts = Post::where('status', 'warning')->has('reports')->get();
        return view('admin.reports.post-index', ['posts' => PostReportResource::collection($posts)]);
    }

    public function banned()
    {
        $posts = Post::where('status', 'ban')->has('reports')->get();
        return view('admin.reports.post-index', ['posts' => PostReportResource::collection($posts)]);
    }

    public function clearAccounts(Request $request)
    {
        $postsIds = $request->get('ids');

        foreach ($postsIds as $postId) {
            $post = Post::find($postId);

            if (!$post) {
                continue;
            }

            if ($post->owner) {
                $owner = $post->owner;
                $owner->status = null;
                $owner->save();
            }

            $post->status = null;
            $post->save();

            foreach ($post->reports as $report) {
                $report->delete();
            }
        }

        return response()->json(['data' => 'success']);
    }

    public function issueAccounts(Request $request)
    {
        $postIds = $request->get('ids');
        foreach ($postIds as $postId) {
            $post = Post::find($postId);

            if ($post) {
                $post->status = 'warning';
                $post->save();

                if ($post->owner) {
                    $owner = $post->owner;
                    $owner->status = 'warning';
                    $owner->warningCount = $owner->warningCount + 1;

                    $owner->save();

                    // Uncomment this when you want to notify
                    // $owner->notify(new IssueWarningNotification());
                }
            }
        }
        //        foreach ($request->get('ids') as $id) {
        //            $user = User::where('id', $id)->first();
        //            $user->status = 'warning';
        //            $user->warningCount = $user->warningCount + 1;
        //            $user->save();
        //            $user->notify(new IssueWarningNotification());
        //
        //        }

        return response()->json(['data' => 'success']);
    }

    public function banAccounts(Request $request)
    {
        foreach ($request->get('ids') as $id) {
            $posts = Post::where('id', $id)->get();
            foreach ($posts as $post) {
                $post->status = 'ban';
                $post->save();
            }

            if ($post->owner) {
                $user = $post->owner;
                $user->status = 'ban';
                $user->save();
            }
        }

        return response()->json(['data' => 'success']);
    }
}
