<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\PostCollection;
use App\Models\Song;
use App\Models\User;
use App\Models\Comment;
use AWS\CRT\Log;
use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;

class CheckMqttComments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:comments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'MQTT Comments import to DB';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $mqtt = MQTT::connection();

        $mqtt->subscribe('comments/#', function ($topic, $payload) {
            $message = json_decode($payload);
            if (!$message || !isset($message->type, $message->userId)) {
                return;
            }

            $type = $message->type;
            $user = User::find($message->userId);
            if (!$user) return;

            // Handle Comments for Posts
            if (str_contains($topic, 'posts')) {
                $post = Post::find($message->forPostId ?? null);
                if (!$post) return;

                if ($type === 'new') {
                    $comment = $post->commentAs($user, $message->message);

                    if ($user->id !== $post->owner_id) {
                        $deepLink = 'EXPOSVRE://postcomment/' . $post->id;

                        \App\Models\Notification::create([
                            'title' => 'commented on your post',
                            'description' => 'commented on your post',
                            'type' => 'postcomment',
                            'user_id' => $post->owner_id,
                            'sender_id' => $user->id,
                            'post_id' => $post->id,
                            'deep_link' => $deepLink,
                        ]);

                        $post->owner->notify(new \App\Notifications\NewCommentForPost($user, $message->message, $post));
                    }

                    // Mentions: @username
                    preg_match_all('/@(\w+)/', $message->message, $matches);
                    foreach ($matches[1] as $username) {
                        $mentioned = User::where('name', $username)->first();
                        if ($mentioned && $mentioned->id !== $user->id) {
                            $mentioned->notify(new \App\Notifications\UserMentioned($user, $comment, $post));
                        }
                    }
                } elseif ($type === 'edit') {
                    $comment = Comment::find($message->commentId ?? null);
                    if ($comment && $comment->user_id === $user->id) {
                        $comment->comment = $message->message;
                        $comment->edited_at = now();
                        $comment->save();
                    }
                } elseif ($type === 'delete') {
                    $comment = Comment::find($message->commentId ?? null);
                    if ($comment) {
                        $isOwner = $comment->user_id === $user->id;
                        $isPostOwner = optional($comment->commentable)->user_id === $user->id;
                        if ($isOwner || $isPostOwner) {
                            $comment->delete();
                        }
                    }
                }

                // Handle Comments for Songs
            } elseif (str_contains($topic, 'songs')) {
                $song = Song::find($message->forSongId ?? null);
                $song->commentAs($user, $message->message);

                // Handle Comments for Galleries (Post Collections)
            } elseif (str_contains($topic, 'galleries')) {
                $collection = PostCollection::find($message->forGalleryId ?? null);
                $collection->commentAs($user, $message->message);

                $deepLink = 'EXPOSVRE://gallerycomment/' . $collection->id;

                \App\Models\Notification::create([
                    'title' => 'commented on your collection',
                    'description' => 'commented on your collection',
                    'type' => 'collectioncomment',
                    'user_id' => $collection->user_id,
                    'sender_id' => $user->id,
                    'post_id' => $collection->id,
                    'deep_link' => $deepLink,
                ]);

                $collection->user->notify(new \App\Notifications\NewCommentForCollection($user, $message->message, $collection));

                // Handle Comments for Profiles
            } elseif (str_contains($topic, 'profiles')) {
                $profileUser = User::find($message->forProfileId ?? null);
                $profileUser->commentAs($user, $message->message);
                $profileUser->notify(new \App\Notifications\NewCommentForUser($user, $message->message, $profileUser));
            }
        }, 0);

        $mqtt->loop(true);


        $mqtt->disconnect();
    }
}
