<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddShippingAddressRequest;
use App\Http\Requests\FeedRequest;
use App\Http\Resources\InterestsResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\TagsSimpleResource;
use App\Http\Resources\UserInfoResource;
use App\Http\Resources\UserMilestoneResource;
use App\Http\Resources\UserSubscriptionResource;
use App\Http\Resources\UserUploadAvatarResource;
use App\Models\InterestsCategory;
use App\Models\Notification;
use App\Models\Post;
use App\Models\Conversation;
use App\Models\Tag;
use App\Models\User;
use App\Models\Block;
use App\Models\InterestsUserAssigment;
use App\Models\UserSettings;
use App\Models\UserShippingAddress;
use App\Notifications\NewSubscription;
use App\Notifications\TestNotificationWithDeepLink;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use function Aws\filter;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        return InterestsResource::collection(auth('api')->user()->interests);
    }

    public function assignInterest(Request $request)
    {
        $interest = InterestsCategory::where([
            'name' => $request->get('interest'),
        ])->first();
        auth('api')
            ->user()
            ->assignInterest($interest->id);
        return InterestsResource::collection(auth('api')->user()->interests);
    }

    public function assignInterestArray(Request $request)
    {
        $interests = $request->get('interests');

        //        $userInterests = auth('api')->user()->interests->map(function ($userInterest) {
        //            return $userInterest->slug;
        //        })->toArray();

        //        dd($userInterests);

        auth('api')
            ->user()
            ->revokeInterests();

        if (is_array($interests)) {
            foreach ($interests as $interest) {
                if ($interest) {
                    $findInterest = InterestsCategory::where([
                        'name' => $interest,
                    ])->first();
                    auth('api')
                        ->user()
                        ->assignInterest($findInterest->id);
                }
            }
        }
        auth('api')
            ->user()
            ->refresh();
        return InterestsResource::collection(auth('api')->user()->interests);
    }

    public function assignNotInterest(Request $request)
    {
        $interest = InterestsCategory::where([
            'slug' => $request->get('interest'),
        ])->first();
        auth('api')
            ->user()
            ->assignNotInterest($interest->id);
        return InterestsResource::collection(auth('api')->user()->notInterests);
    }

    public function assignNotInterestArray(Request $request)
    {
        $interests = $request->get('interests'); // array of names
        $user = auth('api')->user();

        // Remove all current "not interested" assignments
        $user->revokeNotInterests();

        $invalidInterests = [];

        if (is_array($interests)) {
            foreach ($interests as $interestName) {
                if (!$interestName) {
                    continue;
                }

                // Search interest by name
                $interest = InterestsCategory::where('name', $interestName)->first();

                if ($interest) {
                    $user->assignNotInterest($interest->id);
                } else {
                    $invalidInterests[] = $interestName;
                }
            }
        }

        $user->refresh();

        return InterestsResource::collection(auth('api')->user()->notInterests);
    }


    public function assignTag(Request $request)
    {
        $tag = Tag::where('id', '=', $request->get('tag_id'))->first();
        auth('api')
            ->user()
            ->attachTag($tag);
        return TagsSimpleResource::collection(auth('api')->user()->tags);
    }

    public function removeTag(Request $request)
    {
        $tag = Tag::where('id', '=', $request->get('tag_id'))->first();
        auth('api')
            ->user()
            ->detachTag($tag);
        return TagsSimpleResource::collection(auth('api')->user()->tags);
    }

    public function userInfo($id)
    {
        $user = User::where(['id' => $id])->first();
        $currentUser = auth()->user();
        if ($user->hasBlocked($currentUser->id)) {
            return response()->json(
                [
                    'error' =>
                    'You do not have access to this user’s profile because they have blocked you.',
                ],
                403
            );
        }
        if ($user) {
            return new UserInfoResource($user);
        } else {
            return false;
        }
    }

    public function userInfoByUsername($username)
    {
        $user = User::where(['username' => $username])->first();
        $currentUserId = auth('api')->user()->id;

        $isBlocked = Block::where(function ($query) use (
            $currentUserId,
            $user
        ) {
            $query
                ->where('user_id', $user->id)
                ->where('blocking_id', $currentUserId);
        })
            ->orWhere(function ($query) use ($currentUserId, $user) {
                $query
                    ->where('user_id', $currentUserId)
                    ->where('blocking_id', $user->id);
            })
            ->exists();

        if ($isBlocked) {
            return response()->json(
                ['error' => 'Access denied. User profile is blocked.'],
                403
            );
        }
        if ($user) {
            return response()->json(['data' => new UserInfoResource($user)]);
        } else {
            return response()->json(['data' => false]);
        }
    }

    /**
     * Get the ids of posts what was updated after last update date (timestamp)
     *
     * @return JsonResponse Example: {"data":[15,16,17,18,19,20,21,22]}
     */
    public function feed(FeedRequest $request)
    {
        $now = Carbon::now()->setTimezone('US/Eastern')->toDateTimeString();

        $user = auth('api')->user();
        $userInterestsArray = $user->interests()->pluck('slug')->toArray();

        $posts = [];

        if (!empty($userInterestsArray)) {
            $postsInterested = Post::with('interests')
                ->whereHas('interests', function ($query) use ($userInterestsArray) {
                    $query->whereIn('slug', $userInterestsArray);
                })
                ->where('status', '!=', 'archive')
                ->whereDoesntHave('reports')
                ->get()
                ->filter(function ($post) use ($user) {
                    return !$user->isBlocking($post->owner);
                })
                ->filter(function ($post) use ($user) {
                    return !$user->isBlockedBy($post->owner);
                })
                ->pluck('id')
                ->toArray();

            $posts = array_merge($posts, $postsInterested);
        }

        // 2. User's own posts
        $userPosts = $user->posts()
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'archive');
            })
            ->whereDoesntHave('reports')
            ->pluck('id')
            ->toArray();

        $posts = array_merge($posts, $userPosts);

        // 3. Subscription posts
        $subscriptionUserIds = $user->subscriptions->pluck('id')->toArray();
        if (!empty($subscriptionUserIds)) {
            $subscriptionPosts = Post::whereIn('owner_id', $subscriptionUserIds)
                ->whereDoesntHave('reports')
                ->where(function ($query) {
                    $query->whereNull('status')
                        ->orWhere('status', '!=', 'archive');
                })
                ->get()
                ->filter(function ($post) use ($user) {
                    return !$user->isBlocking($post->owner);
                })
                ->filter(function ($post) use ($user) {
                    return !$user->isBlockedBy($post->owner);
                })
                ->pluck('id')
                ->toArray();

            $posts = array_merge($posts, $subscriptionPosts);
        }

        // 4. Additorials (ads)
        $postsAdditorials = Post::where('owner_id', 1)
            ->where('ad', 1)
            ->whereDoesntHave('reports')
            ->where(function ($query) use ($now) {
                $query->whereNull('publish_date')
                    ->orWhere('publish_date', '<=', $now);
            })
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'archive');
            })
            ->pluck('id')
            ->toArray();

        $posts = array_merge($posts, $postsAdditorials);

        // 5. Market posts
        $marketPosts = Post::where('post_for_sale', 1)
            ->where('status', '!=', 'archive')
            ->whereDoesntHave('reports')
            ->get()
            ->filter(function ($post) use ($user) {
                return !$user->isBlocking($post->owner);
            })
            ->filter(function ($post) use ($user) {
                return !$user->isBlockedBy($post->owner);
            })
            ->pluck('id')
            ->toArray();

        $posts = array_merge($posts, $marketPosts);

        // Final collection
        $uniquePostIds = array_unique($posts);

        $sortedPosts = Post::whereIn('id', $uniquePostIds)
            ->orderByRaw("GREATEST(
            COALESCE(publish_date, '1970-01-01'),
            COALESCE(created_at, '1970-01-01')
        ) DESC")
            ->get();

        return response()->json([
            'data' => $sortedPosts->pluck('id')->values()
        ]);
    }

    public function notificationAction(Request $request)
    {
        $notificationId = $request->get('notificationId');
        $action = $request->get('receiverAction');

        $notification = Notification::where(['id' => $notificationId])->first();

        $notification->receiverAction = $action;

        $notification->save();

        return response()->json([
            'data' => new NotificationResource($notification),
        ]);
    }

    public function userPosts($id)
    {
        $user = User::where(['id' => $id])->first();
        $posts = $user->load('posts')->posts;
        $posts = $posts->filter(function ($post) {
            return $post->reports->count() == 0 && $post->status != 'archive';
        });
        return PostResource::collection($posts);
    }

    public function avatarUpload(Request $request)
    {
        $user = auth('api')->user();
        //        $avatar = $request->file('avatar');
        $user->clearMediaCollection('preview');
        $user->addMediaFromRequest('avatar')->toMediaCollection('preview');

        return response()->json([
            'data' => new UserUploadAvatarResource($user),
        ]);
    }

    public function subscribe($id)
    {
        $user = User::where(['id' => $id])->first();
        $subscriber = auth('api')->user();
        $subscriber->subscribe($user);

        $deepLink = 'EXPOSVRE://user/' . $subscriber->id;
        $notification = new \App\Models\Notification();
        $notification->title = 'started following you';
        $notification->description = 'started following you';
        $notification->type = 'subscription';
        $notification->user_id = $user->id;
        $notification->sender_id = $subscriber->id;
        //        $notification->post_id = $this->collection->id;
        $notification->deep_link = $deepLink;
        $notification->save();

        $user->notify(new NewSubscription($user, $subscriber));

        $subscriber->refresh();

        return response()->json($subscriber->subscriptions);
    }

    public function unsubscribe($id)
    {
        $user = User::where(['id' => $id])->first();
        $subscriber = auth('api')->user();
        $subscriber->unsubscribe($user);

        $subscriber->refresh();

        return response()->json($subscriber->subscriptions);
    }

    public function subscriptions()
    {
        $user = auth('api')->user();
        $subscriptions = $user
            ->subscriptions()
            ->whereHas('profile')
            ->whereDoesntHave('blockedBy', function (Builder $query) use (
                $user
            ) {
                $query->where('user_id', $user->id);
            })
            ->whereDoesntHave('blocks', function (Builder $query) use ($user) {
                $query->where('blocking_id', $user->id);
            })
            ->get();
        return response()->json([
            'data' => UserSubscriptionResource::collection($subscriptions),
        ]);
    }

    public function subscribers()
    {
        $user = auth('api')->user();
        $subscribers = $user
            ->subscribers()
            ->whereHas('profile')
            ->whereDoesntHave('blockedBy', function (Builder $query) use (
                $user
            ) {
                $query->where('user_id', $user->id);
            })
            ->whereDoesntHave('blocks', function (Builder $query) use ($user) {
                $query->where('blocking_id', $user->id);
            })
            ->get();

        return response()->json([
            'data' => UserSubscriptionResource::collection($subscribers),
        ]);
    }

    public function subscriptionsAndSubscribersByUserId($id)
    {
        $user = User::where(['id' => $id])->first();

        $subscribers = $user
            ->subscribers()
            ->whereHas('profile')
            ->get();
        $subscriptions = $user
            ->subscriptions()
            ->whereHas('profile')
            ->get();

        return response()->json([
            'data' => [
                'subscriptions' => UserSubscriptionResource::collection(
                    $subscriptions
                ),
                'subscribers' => UserSubscriptionResource::collection(
                    $subscribers
                ),
            ],
        ]);
    }

    public function getAddress(Request $request)
    {
        $formattedAddr = str_replace(' ', '+', $request->get('address'));
        //            $apiKey = 'AIzaSyCfE0q9yqhf693IYWy6RbMPKDikqMidUAc';
        $apiKey = 'AIzaSyDp7ajTsZXBVVpq4i5CaYSc4tqSTtT7R5A';
        //            $geocodeFromAddr = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . $formattedAddr . '&sensor=false&key=' . $apiKey);
        $geocodeFromAddr = file_get_contents(
            'https://maps.googleapis.com/maps/api/place/autocomplete/json?input=' .
                $formattedAddr .
                '&types=geocode&key=' .
                $apiKey
        );

        $output = json_decode($geocodeFromAddr);

        return response()->json(['data' => $output]);
    }

    public function milestones(Request $request)
    {
        return response()->json([
            'data' => new UserMilestoneResource(auth('api')->user()),
        ]);
    }

    public function addShippingAddress(
        AddShippingAddressRequest $shippingAddressRequest
    ) {
        $user = auth('api')->user();
        $shippingAddressRequest->merge(['user_id' => $user->id]);
        $shippingAddress = UserShippingAddress::create(
            $shippingAddressRequest->all()
        );

        return response()->json(['data' => $shippingAddress]);
    }

    public function setToken(Request $request)
    {
        $user = auth('api')->user();
        $user->pushToken = $request->get('pushToken');
        $user->save();

        return response()->json(['data' => $user]);
    }

    public function test()
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user->notify(new TestNotificationWithDeepLink());

        return response()->json(['data' => 'Notification sent successfully']);
    }


    public function getNotificationsList(Request $request)
    {
        $user = auth()->user();

        if ($user->profile) {
            $page = (int) $request->input('page', 1);
            $limit = (int) $request->input('limit', 20);
            $offset = ($page - 1) * $limit;

            $notificationsQuery = $user->notifications()->whereDoesntHave('sender', function ($query) use ($user) {
                $query->whereIn(
                    'id',
                    $user->blocks()->pluck('blocking_id')
                );
            })
                ->orderBy('updated_at', 'desc');

            if ($request->has('page') && $request->has('limit')) {
                $paginatedNotifications = $notificationsQuery
                    ->skip($offset)
                    ->take($limit)
                    ->get();
            } else {
                $paginatedNotifications = $notificationsQuery->get();
            }

            return response()->json([
                'data' => NotificationResource::collection(
                    $paginatedNotifications
                )
            ]);
        }

        return response()->json(['data' => []], 404);
    }

    public function getMarketNotificationsList(Request $request)
    {
        $user = auth()->user();

        if ($user->profile) {
            $page = (int) $request->input('page', 1);
            $limit = (int) $request->input('limit', 20);
            $offset = ($page - 1) * $limit;

            $allowedTypes = [
                'priceRequest',
                'priceRespondedApprove',
                'priceRespondedDecline',
                'priceOffer',
            ];

            $notificationsQuery = $user->notifications()
                ->whereIn('type', $allowedTypes)
                ->whereDoesntHave('sender', function ($query) use ($user) {
                    $query->whereIn(
                        'id',
                        $user->blocks()->pluck('blocking_id')
                    );
                })
                ->orderBy('updated_at', 'desc');

            if ($request->has('page') && $request->has('limit')) {
                $paginatedNotifications = $notificationsQuery
                    ->skip($offset)
                    ->take($limit)
                    ->get();
            } else {
                $paginatedNotifications = $notificationsQuery->get();
            }

            return response()->json([
                'data' => NotificationResource::collection($paginatedNotifications)
            ]);
        }

        return response()->json(['data' => []], 404);
    }
    public function marketLike($id)
    {
        $user = User::where(['id' => $id])->first();
        $profile = $user->profile;
        $like = auth('api')
            ->user()
            ->like($profile);
        return response()->json(['data' => $like]);
    }

    public function marketUnlike($id)
    {
        $user = User::where(['id' => $id])->first();
        $profile = $user->profile;
        $like = auth('api')
            ->user()
            ->unlike($profile);
        return response()->json(['data' => $like]);
    }

    public function setupSettings(Request $request)
    {
        $user = auth('api')->user();
        $data = $request->only([
            'followersHidden',
            'followedHidden',
            'notify_phone_verification',
            'notify_new_message',
            'notify_new_comment',
            'notify_new_crowned_post',
            'notify_new_follow',
            'notify_new_sale',
            'notify_price_request'
        ]);

        if ($user->setting) {
            $user->setting->update($data);
            $setting = $user->setting;
        } else {
            $setting = new UserSettings();
            $setting->fill($data);
            $setting->user_id = $user->id;
            $setting->save();
        }

        return response()->json(['data' => $setting]);
    }

    public function delete()
    {
        $user = auth('api')->user();
        $user->status = 'deleted';
        $user->save();

        return response()->json(['data' => $user->id]);
    }

    public function blockUser($blockUserID)
    {
        $blockUser = User::where(['id' => $blockUserID])->first();

        $userWhoBlock = auth('api')->user();

        Conversation::where(function ($query) use ($blockUserID, $userWhoBlock) {
            $query->where('sender', $userWhoBlock->id)
                ->where('receiver', $blockUserID);
        })
            ->orWhere(function ($query) use ($blockUserID, $userWhoBlock) {
                $query->where('sender', $blockUserID)
                    ->where('receiver', $userWhoBlock->id);
            })
            ->update(['status' => 'inactive']);

        $userWhoBlock->unsubscribe($blockUser);

        $userWhoBlock->block($blockUser->id);


        return response()->json([
            'data' => 'You are blocked user ' . $blockUser->username,
        ]);
    }

    public function blockedList()
    {
        $user = auth('api')->user();
        $blockedUsers = $user->getBlocking();
        $users = [];
        foreach ($blockedUsers as $blockedUser) {
            $users[] = $blockedUser->blocking;
        }
        //        dd($users);

        return response()->json([
            'data' => UserInfoResource::collection($users),
        ]);
    }

    public function unblockUser($blockUserID)
    {
        $blockUser = User::where(['id' => $blockUserID])->first();

        $userWhoBlock = auth('api')->user();

        Conversation::where(function ($query) use ($blockUserID, $userWhoBlock) {
            $query->where('sender', $userWhoBlock->id)
                ->where('receiver', $blockUserID);
        })
            ->orWhere(function ($query) use ($blockUserID, $userWhoBlock) {
                $query->where('sender', $blockUserID)
                    ->where('receiver', $userWhoBlock->id);
            })
            ->update(['status' => 'active']);

        $userWhoBlock->unblock($blockUser->id);

        return response()->json([
            'data' => 'You are unblocked user ' . $blockUser->username,
        ]);
    }



    //-------------------------- API for Hybrid Build ----------------------------//

    public function mainFeed(Request $request)
    {
        $page = max((int) $request->input('page', 1), 1);
        $limit = (int) $request->input('limit', 10);
        $offset = ($page - 1) * $limit;

        $now = Carbon::now()->setTimezone('US/Eastern')->toDateTimeString();
        $user = auth('api')->user();

        $userInterestSlugs = $user->interests()->pluck('slug')->toArray();
        $blockedOwnerIds = $user->blocks()->pluck('blocking_id')
            ->merge($user->blockedBy()->pluck('user_id'))
            ->unique()
            ->toArray();

        $subscribedOwnerIds = $user->subscriptions()->pluck('id')->toArray();
        $baseQuery = Post::query()
            ->select('posts.*')
            ->where(function ($q) use ($user, $userInterestSlugs, $subscribedOwnerIds, $now) {
                $q->where(function ($q1) use ($userInterestSlugs) {
                    $q1->whereHas('interests', fn($q2) => $q2->whereIn('slug', $userInterestSlugs));
                })
                    ->orWhere(function ($q1) use ($user) {
                        $q1->where('owner_id', $user->id);
                    })
                    ->orWhere(function ($q1) use ($subscribedOwnerIds, $now) {
                        $q1->whereIn('owner_id', $subscribedOwnerIds);
                    });
            })
            ->where(function ($q) {
                $q->where(function ($query) {
                    $query->whereNull('status')
                        ->orWhere('status', '!=', 'archive');
                });
            })
            ->whereNotIn('owner_id', $blockedOwnerIds)
            ->whereDoesntHave('reports');

        $editorialQuery = Post::where('owner_id', 1)
            ->where('ad', 1)
            ->where(function ($query) use ($now) {
                $query->whereNull('publish_date')
                    ->orWhere('publish_date', '<=', $now);
            })
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'archive');
            });

        $basePostIds = $baseQuery->pluck('id');
        $editorialPostIds = $editorialQuery->pluck('id');

        $mergedPosts = Post::whereIn('id', $basePostIds->merge($editorialPostIds)->unique())
            ->whereDoesntHave('reports')
            ->orderByDesc('created_at')
            ->pluck('id');

        $total = $mergedPosts->count();
        $paginatedIds = $mergedPosts->slice($offset, $limit)->values();

        $posts = Post::with(['interests', 'owner'])
            ->whereIn('id', $paginatedIds)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => PostResource::collection($posts),
            'meta' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'count' => $posts->count()
            ]
        ]);
    }


    public function editorial(Request $request)
    {
        $page = (int) $request->input('page', 1);
        $limit = (int) $request->input('limit', 10);
        $offset = ($page - 1) * $limit;

        $now = Carbon::now()->setTimezone('US/Eastern')->toDateTimeString();


        $baseQuery = Post::where('owner_id', 1)
            ->where(function ($query) {
                $query->where('status', '!=', 'archive')
                    ->orWhereNull('status')
                    ->orWhere('status', '');
            })
            ->where('publish_date', '<', $now)
            ->where('ad', 1);

        $total = (clone $baseQuery)->count();

        $posts = $baseQuery
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => PostResource::collection($posts),
            'meta' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'count' => $posts->count()
            ]
        ]);
    }


    public function market(Request $request)
    {
        $page = (int) $request->input('page', 1);
        $limit = (int) $request->input('limit', 10);
        $offset = ($page - 1) * $limit;

        $user = auth('api')->user();

        $baseQuery = Post::where('post_for_sale', 1)
            ->where(function ($query) {
                $query->where('status', '!=', 'archive')
                    ->orWhereNull('status')
                    ->orWhere('status', '');
            })
            ->whereDoesntHave('reports')
            ->with(['reports', 'owner'])
            ->orderBy('created_at', 'desc');

        $allPosts = $baseQuery->get();
        $filteredPosts = $allPosts->filter(function ($post) use ($user) {
            if ($post->reports->isNotEmpty()) {
                return false;
            }

            if ($user->isBlocking($post->owner) || $user->isBlockedBy($post->owner)) {
                return false;
            }

            return true;
        });

        $total = $filteredPosts->count();

        $paginatedPosts = $filteredPosts->slice($offset, $limit)->values();

        return response()->json([
            'data' => PostResource::collection($paginatedPosts),
            'meta' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'count' => $paginatedPosts->count(),
            ],
        ]);
    }

    public function deleteAvatar(Request $request)
    {
        $user = auth('api')->user();

        $user->clearMediaCollection('preview');

        return response()->json([
            'message' => 'Avatar deleted successfully',
        ]);
    }

    public function notifictionPreferenceSetting(Request $request)
    {
        $user = auth('api')->user();

        $userSetting = UserSettings::where('user_id', $user->id)->first();

        return response()->json(['data' => $userSetting]);
    }

    public function removeNotInterestArray(Request $request)
    {
        $interests = $request->get('interests');
        $user = auth('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Convert comma-separated string to array if needed
        if (is_string($interests)) {
            $interests = array_map('trim', explode(',', $interests));
        }

        if (!is_array($interests) || empty($interests)) {
            return response()->json(['error' => 'Invalid input. Provide a list of interest names.'], 422);
        }

        $removedIds = [];

        foreach ($interests as $interestName) {
            if (!$interestName) continue;

            $interest = InterestsCategory::whereRaw('LOWER(name) = ?', [strtolower($interestName)])->first();

            if ($interest) {
                $deleted = InterestsUserAssigment::where('user_id', $user->id)
                    ->where('interest_id', $interest->id)
                    ->where('type', 'interest') // or 'not-interest' if needed
                    ->delete();

                if ($deleted) {
                    $removedIds[] = $interest->id;
                }
            }
        }

        return response()->json([
            'data' => $removedIds
        ]);
    }

    public function userProfilePost(FeedRequest $request, $id)
    {
        $now = Carbon::now()->setTimezone('US/Eastern')->toDateTimeString();
        $limit = (int) $request->get('limit', 10); // default limit
        $page = (int) $request->get('page', 1);
        $offset = ($page - 1) * $limit;
        $type = $request->get('type', 'all');

        $user = auth('api')->user();

        if ($type == 'market') {
            $marketPosts = Post::with(['owner'])
                ->where('post_for_sale', 1)
                ->where('owner_id', $id)
                ->where(function ($query) {
                    $query->whereNull('status')
                        ->orWhere('status', '!=', 'archive');
                })
                ->whereDoesntHave('reports')
                ->latest()
                ->get()
                ->filter(function ($post) use ($user) {
                    return !$user->isBlocking($post->owner) && !$user->isBlockedBy($post->owner);
                });

            $total = $marketPosts->count();
            $paginated = $marketPosts->forPage($page, $limit)->values();

            $totalViews = $marketPosts->sum('views_count');
            $totalLikes = $marketPosts->sum(function ($post) {
                return $post->likers()->count();
            });

            return response()->json([
                'data' => PostResource::collection($paginated),
                'meta' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_views' => $totalViews,
                    'total_likes' => $totalLikes,
                ]
            ]);
        }

        $ownPostsQuery = Post::with(['owner'])
            ->where('owner_id', $id)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'archive');
            })
            ->whereDoesntHave('reports')
            ->orderByRaw("GREATEST(COALESCE(publish_date, '1970-01-01'), COALESCE(created_at, '1970-01-01')) DESC");

        $total = $ownPostsQuery->count();
        $paginated = $ownPostsQuery->skip($offset)->take($limit)->get();

        return response()->json([
            'data' => PostResource::collection($paginated),
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
            ]
        ]);
    }
}
