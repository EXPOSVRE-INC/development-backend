<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AccountVerificationResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function index()
    {
        $users = User::with('profile')->get();

        return view('admin.users.index', [
            'users' => AccountVerificationResource::collection($users)
        ]);
    }

    public function show($id)
    {
        $user = User::where(['id' => $id])->with('profile')->first();

        return view('admin.users.view', [
           'user' => $user
        ]);
    }

    public function verify(Request $request)
    {
        $userId = $request->get('userId');
        $verify = $request->get('verify');

        $user = User::where(['id' => $userId])->first();
        $user->verify = $verify;
        $user->save();

        return response()->json($user);
    }
}
