<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Post;
use App\Models\PostCollection;
use App\Models\Song;
use App\Models\User;
use App\Notifications\NewCommentForCollection;
use App\Notifications\NewCommentForPost;
use App\Notifications\NewCommentForUser;
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

        $mqtt->subscribe('comments/#', function ($topic, $message) {
            $message = json_decode($message);

            if (str_contains($topic, 'posts')) {
                $post = Post::where(['id' => $message->forPostId])->first();
                $user = User::where(['id' => $message->userId])->first();

                if (!$post || !$user) {
                    return;
                }

                $alreadyCommented = $post->comments()
                    ->where('user_id', $user->id)
                    ->where('comment', $message->message)
                    ->where('created_at', '>=', now()->subSeconds(5))
                    ->exists();

                if ($alreadyCommented) {
                    return;
                }

                $post->commentAs($user, $message->message);

                if ($user->id === $post->owner_id) {
                    return;
                }

                $alreadyNotified = Notification::where([
                    'type' => 'postcomment',
                    'sender_id' => $user->id,
                    'post_id' => $post->id,
                ])->where('created_at', '>=', now()->subSeconds(5))
                    ->exists();

                if (!$alreadyNotified) {
                    $post->owner->notify(
                        new NewCommentForPost($user, $message->message, $post)
                    );
                }
            } else if (str_contains($topic, 'songs')) {
                $song = Song::where(['id' => $message->forSongId])->first();
                $user = User::where(['id' => $message->userId])->first();
                $song->commentAs($user, $message->message);
            } else if (str_contains($topic, 'galleries')) {

                if (
                    empty($message->forGalleryId) ||
                    empty($message->userId) ||
                    empty($message->message)
                ) {
                    return;
                }

                $collection = PostCollection::find($message->forGalleryId);
                $user = User::find($message->userId);

                if (!$collection || !$user) {
                    return;
                }

                // Prevent duplicate comments
                $alreadyCommented = $collection->comments()
                    ->where('user_id', $user->id)
                    ->where('comment', $message->message)
                    ->where('created_at', '>=', now()->subSeconds(5))
                    ->exists();

                if ($alreadyCommented) {
                    return;
                }

                $collection->commentAs($user, $message->message);

                // Do not notify self
                if ($collection->user_id === $user->id) {
                    return;
                }

                // Prevent duplicate notifications
                $alreadyNotified = Notification::where([
                    'type' => 'collectioncomment',
                    'user_id' => $collection->user_id,
                    'sender_id' => $user->id,
                    'post_id' => $collection->id,
                ])->exists();

                if ($alreadyNotified) {
                    return;
                }

                $collection->user->notify(
                    new NewCommentForCollection($user, $message->message, $collection)
                );
            } else if (str_contains($topic, 'profiles')) {
                $user = User::where(['id' => $message->forProfileId])->first();

                $comments = $user->comments;
                dump('USER');
                dump($message->message);
                dump($user->id);
                $userWhoComment = User::where(['id' => $message->userId])->first();
                $user->commentAs($userWhoComment, $message->message);
                $user->notify(new NewCommentForUser($userWhoComment, $message->message, $user, null));
            }
        }, 0);

        $mqtt->loop(true);

        $mqtt->disconnect();
    }
}
