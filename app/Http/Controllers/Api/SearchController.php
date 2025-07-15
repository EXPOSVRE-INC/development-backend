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
use App\Models\Block;
use App\Models\User;
use App\Models\Tag;

class SearchController extends Controller
{
    public function index(SearchRequest $request)
    {
        $query = $request->get('query');
        $type = $request->get('type');

        $currentUser = auth('api')->user();

        if ($type == 'posts') {
            $blockedUserIds = Block::where('user_id', $currentUser->id)
                ->pluck('blocking_id')
                ->toArray();

            $blockedByUserIds = Block::where('blocking_id', $currentUser->id)
                ->pluck('user_id')
                ->toArray();

            $excludedUserIds = array_unique(array_merge($blockedUserIds, $blockedByUserIds));

            $searchTerm = $query;

            $postsQuery = Post::where(function ($query) use ($searchTerm) {
                $query->where('title', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('description', 'LIKE', '%' . $searchTerm . '%');
            })->whereNotIn('owner_id', $excludedUserIds)
                ->where(function ($query) {
                    $query->where('status', '!=', 'archive')
                        ->orWhereNull('status');
                });

            if ($request->get('status') && $request->get('status') != '') {
                $statuses = $request->get('status');
                if (count($statuses) < 2) {
                    if (in_array('collectible', $statuses)) {
                        $postsQuery->where('collection_post', 1);
                    }
                    if (in_array('not collectible', $statuses)) {
                        $postsQuery->where('collection_post', 0);
                    }
                }
            }

            if ($request->get('post_type') && $request->get('post_type') != '') {
                $post_types = $request->get('post_type');
                if (in_array('image', $post_types)) {
                    $postsQuery->where('type', 'image');
                }
            }

            if ($request->get('currency') && $request->get('currency') != '') {
                $postsQuery->where('currency', $request->get('currency'));
            }

            if ($request->get('interests') && $request->get('interests') != '') {
                $interests = array_filter($request->get('interests'), function ($value) {
                    return !is_null($value) && $value !== '';
                });
                if (count($interests) > 0) {
                    $postsQuery->whereHas('interests', function ($query) use ($interests) {
                        $query->whereIn('slug', $interests);
                    });
                }
            }

            $minPrice = $request->input('min_price');
            if (!is_null($minPrice) && $minPrice !== '') {
                $postsQuery->where('fixed_price', '>=', $minPrice);
            }

            $maxPrice = $request->input('max_price');
            if (!is_null($maxPrice) && $maxPrice !== '') {
                $postsQuery->where('fixed_price', '<=', $maxPrice);
            }

            $posts = $postsQuery->limit(100)->get();

            return response()->json([
                'data' => PostResource::collection($posts),
            ]);
        } elseif ($type == 'people') {
            $blockedUserIds = Block::where('user_id', $currentUser->id)
                ->pluck('blocking_id')
                ->toArray();


            $blockedByUserIds = Block::where('blocking_id', $currentUser->id)
                ->pluck('user_id')
                ->toArray();


            $searchTerm = $query; // Store the search term in a separate variable

            $peopleQuery = User::where(function ($q) use ($searchTerm) {
                $q->where('username', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhereHas('profile', function ($profile) use ($searchTerm) {
                        return $profile
                            ->where('firstName', 'LIKE', '%' . $searchTerm . '%')
                            ->orWhere('lastName', 'LIKE', '%' . $searchTerm . '%');
                    });
            });

            if (!empty($blockedByUserIds)) {
                $peopleQuery->whereNotIn('id', $blockedByUserIds);
            }

            if (in_array($currentUser->id, $blockedUserIds)) {
                $peopleQuery->whereNotIn('id', $blockedUserIds);
            }
            $people = $peopleQuery->limit(100)->get();

            return response()->json([
                'data' => UserInfoResource::collection($people),
            ]);
        } elseif ($type == 'hashtags') {
            $tags = Tag::where('name', 'LIKE', '%' . $query . '%')->get();
            return response()->json([
                'data' => TagsResource::collection($tags),
            ]);
        } elseif ($type == 'tags') {
            $interests = InterestsCategory::where('slug', 'LIKE', '%' . $query . '%')->get();
            return response()->json([
                'data' => InterestsCategoryResource::collection($interests),
            ]);
        }
    }
    public function searchData(SearchRequest $request)
    {
        $query = $request->get('query');
        $type = $request->get('type');

        $limit = (int) $request->get('limit', 10);
        $page = (int) $request->get('page', 1);
        $offset = ($page - 1) * $limit;

        $currentUser = auth('api')->user();

        if ($type == 'posts') {
            $blockedUserIds = Block::where('user_id', $currentUser->id)->pluck('blocking_id')->toArray();
            $blockedByUserIds = Block::where('blocking_id', $currentUser->id)->pluck('user_id')->toArray();
            $excludedUserIds = array_unique(array_merge($blockedUserIds, $blockedByUserIds));
            $searchTerm = $query;

            $postsQuery = Post::where(function ($query) use ($searchTerm) {
                $query->where('title', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('description', 'LIKE', '%' . $searchTerm . '%');
            })->whereNotIn('owner_id', $excludedUserIds)
                ->where(function ($query) {
                    $query->where('status', '!=', 'archive')
                        ->orWhereNull('status');
                });

            if ($request->get('status') && $request->get('status') != '') {
                $statuses = $request->get('status');
                if (count($statuses) < 2) {
                    if (in_array('collectible', $statuses)) {
                        $postsQuery->where('collection_post', 1);
                    }
                    if (in_array('not collectible', $statuses)) {
                        $postsQuery->where('collection_post', 0);
                    }
                }
            }

            if ($request->get('post_type') && $request->get('post_type') != '') {
                $post_types = $request->get('post_type');
                if (in_array('image', $post_types)) {
                    $postsQuery->where('type', 'image');
                }
            }

            if ($request->get('currency') && $request->get('currency') != '') {
                $postsQuery->where('currency', $request->get('currency'));
            }

            if ($request->get('interests') && $request->get('interests') != '') {
                $interests = array_filter($request->get('interests'), fn($value) => !is_null($value) && $value !== '');
                if (count($interests) > 0) {
                    $postsQuery->whereHas('interests', fn($query) => $query->whereIn('slug', $interests));
                }
            }

            $minPrice = $request->input('min_price');
            if (!is_null($minPrice) && $minPrice !== '') {
                $postsQuery->where('fixed_price', '>=', $minPrice);
            }

            $maxPrice = $request->input('max_price');
            if (!is_null($maxPrice) && $maxPrice !== '') {
                $postsQuery->where('fixed_price', '<=', $maxPrice);
            }

            $posts = $postsQuery->skip($offset)->take($limit)->get();

            return response()->json([
                'data' => PostResource::collection($posts),
                'meta' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $postsQuery->count(),
                ],
            ]);
        } elseif ($type == 'people') {
            $blockedUserIds = Block::where('user_id', $currentUser->id)->pluck('blocking_id')->toArray();
            $blockedByUserIds = Block::where('blocking_id', $currentUser->id)->pluck('user_id')->toArray();
            $searchTerm = $query;

            $peopleQuery = User::where(function ($q) use ($searchTerm) {
                $q->where('username', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhereHas('profile', function ($profile) use ($searchTerm) {
                        return $profile->where('firstName', 'LIKE', '%' . $searchTerm . '%')
                            ->orWhere('lastName', 'LIKE', '%' . $searchTerm . '%');
                    });
            });

            if (!empty($blockedByUserIds)) {
                $peopleQuery->whereNotIn('id', $blockedByUserIds);
            }

            if (in_array($currentUser->id, $blockedUserIds)) {
                $peopleQuery->whereNotIn('id', $blockedUserIds);
            }

            $people = $peopleQuery->skip($offset)->take($limit)->get();

            return response()->json([
                'data' => UserInfoResource::collection($people),
                'meta' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $peopleQuery->count(),
                ],
            ]);
        } elseif ($type == 'hashtags') {
            $tags = Tag::where('name', 'LIKE', '%' . $query . '%')
                ->skip($offset)->take($limit)->get();

            return response()->json([
                'data' => TagsResource::collection($tags),
                'meta' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => Tag::where('name', 'LIKE', '%' . $query . '%')->count(),
                ],
            ]);
        } elseif ($type == 'tags') {
            $interests = InterestsCategory::where('slug', 'LIKE', '%' . $query . '%')
                ->skip($offset)->take($limit)->get();

            return response()->json([
                'data' => InterestsCategoryResource::collection($interests),
                'meta' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => InterestsCategory::where('slug', 'LIKE', '%' . $query . '%')->count(),
                ],
            ]);
        }
    }
}
