<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\UserReportsResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AccountController extends Controller
{
    public function flagged()
    {
        $user = auth('web')->user();
//        $users = User::has('reports')->get();

        $users = User::where(['status' => 'flagged'])->with(['reports'])->get();

        return view('admin.accounts.index', ['users' => UserReportsResource::collection($users)]);
    }

    public function warnings()
    {
//        $users = User::has('reports')->get();

        $users = User::where(['status' => 'warning'])->with(['reports'])->get();

        return view('admin.accounts.index', ['users' => UserReportsResource::collection($users)]);
    }

    public function suspend()
    {
//        $users = User::has('reports')->get();

        $users = User::where(['status' => 'suspend'])->with(['reports'])->get();

        return view('admin.accounts.index', ['users' => UserReportsResource::collection($users)]);
    }

    public function banned()
    {
//        $users = User::has('reports')->get();

        $users = User::where(['status' => 'ban'])->with(['reports'])->get();

        return view('admin.accounts.index', ['users' => UserReportsResource::collection($users)]);
    }
}
