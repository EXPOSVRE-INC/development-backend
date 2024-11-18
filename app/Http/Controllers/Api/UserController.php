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
use App\Models\UserSettings;
use App\Models\UserShippingAddress;
use App\Notifications\NewSubscription;
use App\Notifications\TestNotificationWithDeepLink;
use AWS\CRT\Log;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use function Aws\filter;

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
        $interests = $request->get('interests');
        auth('api')
            ->user()
            ->revokeNotInterests();

        if (is_array($interests)) {
            foreach ($interests as $interest) {
                if ($interest) {
                    $findInterest = InterestsCategory::where([
                        'name' => $interest,
                    ])->first();
                    auth('api')
                        ->user()
                        ->assignNotInterest($findInterest->id);
                }
            }
        }
        auth('api')
            ->user()
            ->refresh();
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
        $fromDate = Carbon::createFromTimestamp(
            $request->get('last_update_date')
        )->toDateTimeString();
        $now = Carbon::now()->toDateTimeString();

        $user = auth('api')->user();

        $userInterests = $user->interests()->get();
        $userInterestsArray = $userInterests
            ->map(function ($interest) {
                return $interest->slug;
            })
            ->toArray();

        //    dd($userInterestsArray);

        $posts = [];

        //        \DB::enableQueryLog();
        $postsInterested = Post::with('interests')
            ->whereHas('interests', function ($query) use (
                $userInterestsArray
            ) {
                $query->whereIn('slug', $userInterestsArray);
            })
            ->get()
            ->filter(function ($post) use ($user) {
                return !$user->isBlocking($post->owner);
            })
            ->filter(function ($post) use ($user) {
                return !$user->isBlockedBy($post->owner);
            })
            ->map(function (Post $post) {
                return $post->id;
            });
        //            ->whereIn('interests_category.slug', $userInterestsArray)->first();
        //        dump(\DB::getQueryLog());
        //        dd($posts);
        //        $posts = Post::where('updated_at', '>=', $fromDate)->where('owner_id', $user->id)->get('id')->map(function (Post $post) {
        //            return $post->id;
        //        })->toArray();
        $posts = array_merge(
            $posts,
            $user->posts
                ->filter(function ($post) {
                    return $post->status == null;
                })
                ->filter(function ($post) {
                    return $post->reports->count() == 0;
                })
                ->filter(function ($post) use ($fromDate) {
                    return $post->updated_at >= $fromDate;
                })
                ->map(function (Post $post) {
                    return $post->id;
                })
                ->toArray()
        );

        foreach ($user->subscriptions as $subscriptionUser) {
            $newPosts = $subscriptionUser->posts
                ->filter(function ($post) {
                    return $post->reports->count() == 0;
                })
                //                ->filter(function ($post) use ($fromDate) {
                //                    return $post->updated_at >= $fromDate;
                //                })
                ->filter(function ($post) use ($now) {
                    if ($post->publish_date != null) {
                        return $post->publish_date < $now;
                    } else {
                        return true;
                    }
                })
                ->filter(function ($post) use ($user) {
                    return !$user->isBlocking($post->owner);
                })
                ->filter(function ($post) use ($user) {
                    return !$user->isBlockedBy($post->owner);
                })
                ->filter(function ($post) {
                    return $post->status == null;
                })
                ->map(function (Post $post) {
                    return $post->id;
                })
                ->toArray();
            $posts = array_merge($posts, $newPosts);
        }

        $postsAdditorials = Post::where(['owner_id' => 1])
            ->where(['status' => null])
            ->where('publish_date', '<', $now)
            //            ->where(['ad' => 1])
            ->get()
            //            ->filter(function ($post) {
            //                return $post->reports->count() == 0;
            //            })
            ->filter(function ($post) use ($fromDate) {
                return $post->updated_at >= $fromDate;
            })
            ->filter(function ($post) {
                return $post->status == null;
            })
            ->map(function (Post $post) {
                return $post->id;
            })
            ->toArray();

        $marketPosts = Post::where(['post_for_sale' => 1])
            ->where(['status' => null])
            ->get()
            ->filter(function ($post) {
                return $post->reports->count() == 0;
            })
            ->filter(function (Post $post) use ($user) {
                return !$user->isBlocking($post->owner);
            })
            ->filter(function ($post) use ($user) {
                return !$user->isBlockedBy($post->owner);
            })
            ->filter(function ($post) use ($fromDate) {
                return $post->updated_at >= $fromDate;
            })
            ->map(function (Post $post) {
                return $post->id;
            })
            ->toArray();

        $posts = array_merge($posts, $postsAdditorials);
        $posts = array_merge($posts, $marketPosts);
        //        if (count($posts) == 0) {
        //            $interests = $user->interests->map(function ($interest) {
        //                return $interest->id;
        //            });
        //
        //            $posts = Post::whereHas('interests', function ($query) use ($interests) {
        //                return $query->whereIn('interests_category.id', $interests);
        //            })->limit(50)->get()->map(function (Post $post) {
        //                return $post->id;
        //            })->toArray();
        //        }
        //
        //        if (count($posts) == 0) {
        //            $posts = Post::where('id', '>', 0)->get('id')->map(function (Post $post) {
        //                return $post->id;
        //            })->toArray();
        //        }
        //        $posts = $posts->filter(function ($post) {
        //            return $post->reports->count() == 0;
        //        });

        rsort($posts);

        $posts = array_values(array_unique($posts, SORT_DESC));

        $newArray = [];
        //        dump($postsInteresed);

        //        foreach ($posts as $key => $post) {
        //            if (!($key % 4) && $key != 0) {
        //                foreach ($postsInteresed as $interestKey => $postInterest) {
        //                    $newArray[] = [$postsInteresed[$interestKey], 'interest'];
        //                }
        //            } else {
        //                $newArray[] = [$posts[$key], 'regular'];
        //            }
        //        };
        $posts = collect($posts);

        $postsInterested =
            count($postsInterested) > 0
                ? array_values($postsInterested->diff($posts)->toArray())
                : [];

        rsort($postsInterested);
        //        dump($postsInterested);
        //        dump($posts);

        $newArray = $posts
            ->chunk(2)
            ->map(function ($items, $key) use ($postsInterested) {
                if (array_key_exists($key, $postsInterested)) {
                    return $items->push($postsInterested[$key]);
                } else {
                    return $items;
                }
            })
            ->collapse();

        return response()->json(['data' => $newArray]);
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
            return $post->reports->count() == 0;
        });
        return PostResource::collection($posts);
    }

    public function avatarUpload(Request $request)
    {
        $user = auth('api')->user();
        $s3FolderPath = 'post-uploads';
        //        $avatar = $request->file('avatar');
        $user->clearMediaCollection('preview');
        $user->addMediaFromRequest('avatar')->withCustomProperties(['folder' => $s3FolderPath])->toMediaCollection('preview','s3');

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
        $user = User::where(['id' => 316])->first();
        $user->notify(new TestNotificationWithDeepLink());
        return response()->json(['data' => 'success']);
        //        $options = [
        //            'key_id' => '243Y27KXL3', // The Key ID obtained from Apple developer account
        //            'team_id' => 'Y82G5S669P', // The Team ID obtained from Apple developer account
        //            'app_bundle_id' => 'com.viaduct.exposure.dev', // The bundle ID for app obtained from Apple developer account
        //            'private_key_path' => base_path() . '/AuthKey_243Y27KXL3.p8', // Path to private key
        ////                'private_key_secret' => null // Private key secret
        //        ];
        //        $customClient = new Client(Token::create($options), true);
        //
        //        $message = ApnMessage::create()
        //            ->title('Account approved')
        //            ->body("Your account was approved!")
        //            ->via($customClient);
        //
        //        dump($message);
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
        if ($user->setting) {
            $user->setting->update(
                $request->only('followersHidden', 'followedHidden')
            );
            $setting = $user->setting;
        } else {
            $setting = new UserSettings();
            $setting->user_id = $user->id;
            $setting->followersHidden = $request->get('followersHidden');
            $setting->followedHidden = $request->get('followedHidden');
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
}
