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
    public function postReports() {
        $posts = Post::whereHas('reports')
        ->whereHas('owner', function ($query) {
            $query->where('status', 'flagged');
        })
        ->get();
        return view('admin.reports.post-index', ['posts' => PostReportResource::collection($posts)]);

    }

    public function warnings()
    {
        $posts = Post::whereHas('reports')
        ->whereHas('owner', function ($query) {
            $query->where('status', 'warning');
        })
        ->get();
        return view('admin.reports.post-index', ['posts' => PostReportResource::collection($posts)]);
    }

    public function banned()
    {
        $posts = Post::whereHas('reports')
        ->whereHas('owner', function ($query) {
            $query->where('status', 'ban');
        })
        ->get();
        return view('admin.reports.post-index', ['posts' => PostReportResource::collection($posts)]);
    }

    public function clearAccounts(Request $request) {

        $postsIds = $request->get('ids');

        foreach ($postsIds as $postId) {
            $post = Post::where(['id' => $postId]);
            $owner = $post->owner;
            $owner->status = null;
            $owner->save();
            foreach ($post->reports as $report) {
                $report->remove();
            }
        }

        return response()->json(['data' => 'success']);
    }

    public function issueAccounts(Request $request) {
        $postsIds = $request->get('ids');

        foreach ($postsIds as $postId) {
            $post = Post::where(['id' => $postId]);
            $post->status = 'warning';
            $post->save();

            $owner = $post->owner;
            $owner->status = 'warning';
            $owner->warningCount = $owner->warningCount + 1;

            $owner->save();
//            $owner->notify(new IssueWarningNotification());
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

    public function banAccounts(Request $request) {
        foreach ($request->get('ids') as $id) {
            $post = Post::where(['owner_id' => $id]);
            $post->status = 'ban';
            $post->save();


            $user = User::where('id', $id)->first();
            $user->status = 'ban';
            $user->save();
        }

        return response()->json(['data' => 'success']);
    }
}
