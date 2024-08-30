<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PostReportResource;
use App\Models\Report;
use App\Models\User;
use App\Notifications\IssueWarningNotification;
use Illuminate\Http\Request;
use Orkhanahmadov\LaravelCommentable\Models\Comment;

class CommentReportController extends Controller
{
    public function commentReports() {
        $reports = Report::where(['model' => 'comment'])->get();

        foreach ($reports as $report) {
            $comment = Comment::where(['id' => $report->model_id])->orderBy('id', 'DESC')->first();
            $report->comment = $comment;
            $user = User::where(['id' => $comment->user_id])->with('profile')->first();
            $report->user = $user;
        }

//        dd($report);

        return view('admin.reports.comments-index', ['reports' => $reports]);

    }

    public function clearAccounts(Request $request) {

        foreach ($request->get('ids') as $id) {
            $user = User::where('id', $id)->first();
            $user->status = null;
            $user->save();
        }

        return response()->json(['data' => 'success']);
    }

    public function issueAccounts(Request $request) {
        foreach ($request->get('ids') as $id) {
            $user = User::where('id', $id)->first();
            $user->status = 'warning';
            $user->warningCount = $user->warningCount + 1;
            $user->save();
//            $user->notify(new IssueWarningNotification());

        }

        return response()->json(['data' => 'success']);
    }

    public function banAccounts(Request $request) {
        foreach ($request->get('ids') as $id) {
            $user = User::where('id', $id)->first();
            $user->status = 'ban';
            $user->save();
        }

        return response()->json(['data' => 'success']);
    }
}
