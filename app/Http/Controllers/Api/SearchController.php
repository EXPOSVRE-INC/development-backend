<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\InterestsCategoryResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\TagsResource;
use App\Http\Resources\UserInfoResource;
use App\Models\InterestsCategory;
use App\Models\Post;
use App\Models\User;
use App\Models\Tag;

class SearchController extends Controller
{

    public function index(SearchRequest $request) {

        $query = $request->get('query');
        $type = $request->get('type');

        \Log::info(json_encode($request->all()));

        if ($type == 'posts') {
            $posts = Post::where('title', 'LIKE', '%'.$query.'%');
            $posts->orWhere('description', 'LIKE', '%'.$query.'%');
                if($request->get('status') && $request->get('status') != '') {
//                    $statuses = json_decode($request->get('status'));
                    $statuses = $request->get('status');
                    if (count($statuses) < 2) {
                        if (in_array('collectible', $statuses)) {
                            $posts->where(['collection_post' => 1]);
                        }
                        if (in_array('not collectible', $statuses)) {
                            $posts->where(['collection_post' => 0]);
                        }
                    }
                }
                if ($request->get('post_type') && $request->get('post_type') != '') {
//                    $post_types = json_decode($request->get('post_type'));
                    $post_types = $request->get('post_type');
                    if (in_array('image', $post_types)) {
                        $posts->where(['type' => 'image']);
                    }
                }
                if ($request->get('currency') && $request->get('currency') != '') {
                    $posts->where(['currency' => $request->get('currency')]);
                }
                if ($request->get('interests') && $request->get('interests') != '') {
//                    $interests = json_decode($request->get('interests'));
                    $interests = $request->get('interests');
                    $interests = array_filter($interests, function($value) {
                        return !is_null($value) && $value !== '';
                    } );
                    if (count($interests) > 0 && !empty($interests)) {
                        $posts->whereHas('interests', function ($query) use ($interests) {
                            $query->whereIn('slug', $interests);
                        });
                    }
                }
            $posts->where('fixed_price', '>=', $request->input('min_price'));
            $posts->where('fixed_price', '<=', $request->input('max_price'));
                    $posts = $posts->limit(100)->get();
            return response()->json(['data' => PostResource::collection($posts)]);
        } else if ($type == 'people') {
            $people = User::where('username', 'LIKE', '%'.$query.'%')->orWhereHas('profile', function ($profile) use ($query) {
                return $profile->where('firstName', 'LIKE', '%'.$query.'%')
                    ->orWhere('lastName', 'LIKE', '%'.$query.'%');
            })->limit(100)->get();
            return response()->json(['data' => UserInfoResource::collection($people)]);
        } else if ($type == 'hashtags') {
            $tags = Tag::where('name', 'LIKE', '%'.$query.'%')->get();
            return response()->json(['data' => TagsResource::collection($tags)]);
        } else if ($type == 'tags') {
            $interests = InterestsCategory::where('slug', 'LIKE', '%'.$query.'%')->get();
            return response()->json(['data' => InterestsCategoryResource::collection($interests)]);
        }
    }
}
