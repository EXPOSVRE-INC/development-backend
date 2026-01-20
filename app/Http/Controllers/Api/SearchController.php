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

            $blockedUserIds = Block::where('user_id', $currentUser->id)->pluck('blocking_id')->toArray();
            $blockedByUserIds = Block::where('blocking_id', $currentUser->id)->pluck('user_id')->toArray();
            $excludedUserIds = array_unique(array_merge($blockedUserIds, $blockedByUserIds));

            $searchTerm = $query;

            $postsQuery = Post::whereNotIn('owner_id', $excludedUserIds)
                ->where(function ($q) use ($searchTerm) {

                    $q->where('title', 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere('description', 'LIKE', '%' . $searchTerm . '%')

                        ->orWhereHas('owner', function ($owner) use ($searchTerm) {
                            $owner->where('username', 'LIKE', '%' . $searchTerm . '%')
                                ->orWhereHas('profile', function ($profile) use ($searchTerm) {
                                    $profile->where('firstName', 'LIKE', '%' . $searchTerm . '%')
                                        ->orWhere('lastName', 'LIKE', '%' . $searchTerm . '%');
                                });
                        });
                })
                ->where('is_archived', false)
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
                $interests = array_filter($request->get('interests'), fn($v) => !is_null($v) && $v !== '');
                if (count($interests) > 0) {
                    $postsQuery->whereHas(
                        'interests',
                        fn($query) =>
                        $query->whereIn('slug', $interests)
                    );
                }
            }

            if ($request->filled('min_price')) {
                $postsQuery->where('fixed_price', '>=', $request->min_price);
            }

            if ($request->filled('max_price')) {
                $postsQuery->where('fixed_price', '<=', $request->max_price);
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


            $searchTerm = trim($query);
            $parts = preg_split('/\s+/', $searchTerm);
            $firstPart = $parts[0] ?? null;
            $lastPart  = $parts[1] ?? null;

            $peopleQuery = User::where(function ($q) use ($searchTerm, $firstPart, $lastPart) {
                $q->where('username', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhereHas('profile', function ($profile) use ($searchTerm, $firstPart, $lastPart) {
                        $profile->where('firstName', 'LIKE', '%' . $searchTerm . '%')
                            ->orWhere('lastName', 'LIKE', '%' . $searchTerm . '%');

                        if ($firstPart && $lastPart) {
                            $profile->orWhere(function ($q2) use ($firstPart, $lastPart) {
                                $q2->where('firstName', 'LIKE', '%' . $firstPart . '%')
                                    ->where('lastName',  'LIKE', '%' . $lastPart  . '%');
                            });
                        }
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

            $postsQuery = Post::whereNotIn('owner_id', $excludedUserIds)
                ->where(function ($q) use ($searchTerm) {

                    $q->where('title', 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere('description', 'LIKE', '%' . $searchTerm . '%')

                        ->orWhereHas('owner', function ($owner) use ($searchTerm) {
                            $owner->where('username', 'LIKE', '%' . $searchTerm . '%')
                                ->orWhereHas('profile', function ($profile) use ($searchTerm) {
                                    $parts = preg_split('/\s+/', trim($searchTerm));
                                    $first = $parts[0] ?? null;
                                    $last  = $parts[1] ?? null;

                                    $profile->where('firstName', 'LIKE', '%' . $searchTerm . '%')
                                        ->orWhere('lastName', 'LIKE', '%' . $searchTerm . '%');

                                    if ($first && $last) {
                                        $profile->orWhere(function ($q2) use ($first, $last) {
                                            $q2->where('firstName', 'LIKE', '%' . $first . '%')
                                                ->where('lastName',  'LIKE', '%' . $last  . '%');
                                        });
                                    }
                                });
                        });
                })
                ->where('is_archived', false)
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
                $interests = array_filter($request->get('interests'), fn($v) => !is_null($v) && $v !== '');
                if (count($interests) > 0) {
                    $postsQuery->whereHas(
                        'interests',
                        fn($query) =>
                        $query->whereIn('slug', $interests)
                    );
                }
            }

            if ($request->filled('min_price')) {
                $postsQuery->where('fixed_price', '>=', $request->min_price);
            }

            if ($request->filled('max_price')) {
                $postsQuery->where('fixed_price', '<=', $request->max_price);
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
            $searchTerm = trim($query);
            $parts = preg_split('/\s+/', $searchTerm);
            $firstPart = $parts[0] ?? null;
            $lastPart  = $parts[1] ?? null;

            $peopleQuery = User::where(function ($q) use ($searchTerm, $firstPart, $lastPart) {
                $q->where('username', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhereHas('profile', function ($profile) use ($searchTerm, $firstPart, $lastPart) {
                        $profile->where('firstName', 'LIKE', '%' . $searchTerm . '%')
                            ->orWhere('lastName', 'LIKE', '%' . $searchTerm . '%');

                        if ($firstPart && $lastPart) {
                            $profile->orWhere(function ($q2) use ($firstPart, $lastPart) {
                                $q2->where('firstName', 'LIKE', '%' . $firstPart . '%')
                                    ->where('lastName',  'LIKE', '%' . $lastPart  . '%');
                            });
                        }
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
