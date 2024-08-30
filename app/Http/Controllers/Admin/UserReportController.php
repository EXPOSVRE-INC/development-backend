<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PostReportResource;
use App\Http\Resources\Admin\UserReportsResource;
use App\Models\Post;
use App\Models\User;
use App\Notifications\IssueWarningNotification;
use Illuminate\Http\Request;

class UserReportController extends Controller
{
    public function userReports() {
        $users = User::has('reports')->get();

        return view('admin.reports.index', ['users' => UserReportsResource::collection($users)]);
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
