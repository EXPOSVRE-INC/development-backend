<?php

namespace App\Console\Commands;

use App\Models\Report;
use App\Models\User;
use Illuminate\Console\Command;
use Orkhanahmadov\LaravelCommentable\Models\Comment;
use PhpMqtt\Client\Facades\MQTT;

class CheckMqttReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        $mqtt->subscribe('reports/#', function ($topic, $message) {
            $message = json_decode($message);
//            dump($message);
            if (str_contains($topic, 'profile')) {
                $report = new Report();
                $report->reason = '';
                $report->status = 'flagged';
                $report->reporter_id = $message->userId;
                $report->model = 'user';
                $report->model_id = $message->profile->user_id;
                $report->save();

                $user = User::where(['id' => $message->profile->user_id])->first();
                $user->status = 'flagged';
                $user->save();
//                dump($user);
            } else if (str_contains($topic, 'post')) {
                $report = new Report();
                $report->reason = $message->reason ?? '';
                $report->status = 'flagged';
                $report->reporter_id = $message->userId;
                $report->model = 'post';
                $report->model_id = $message->post->id;
                $report->save();

                $post = Post::find($message->post->id);
                if ($post) {
                    $post->status = 'flagged';
                    $post->save();
                }
                
                $user = User::where(['id' => $message->profile->user_id])->first();
                $user->status = 'flagged';
                $user->save();
            }
            else if (str_contains($topic, 'opinions')) {
                $comment = Comment::where(['commentable_type' => 'App\Models\Song'])
                    ->where(['commentable_id' => $message->message->forSongId])
                    ->where(['user_id' => $message->message->userId])
                    ->where(['comment' => $message->message->message])
                    ->first();

                $report = new Report();
                $report->reason = $message->message->payload->reason ?? '';
                $report->status = 'flagged';
                $report->reporter_id = $message->userId;
                $report->model = 'comment';
                $report->model_id = $comment->id;
                $report->save();
            }
            else if (str_contains($topic, 'comments')) {
                $comment = Comment::where(['commentable_type' => 'App\Models\Post'])
                    ->where(['commentable_id' => $message->message->forPostId])
                    ->where(['user_id' => $message->message->userId])
                    ->where(['comment' => $message->message->message])
                    ->first();

                dump($message->message->userId);
                dump($comment);

                $report = new Report();
                $report->reason = $message->message->payload->reason;
                $report->status = 'flagged';
                $report->reporter_id = $message->userId;
                $report->model = 'comment';
                $report->model_id = $comment->id;
                $report->save();

                $user = User::where(['id' => $message->userId])->first();
                $user->status = 'flagged';
                $user->save();

//                dump($report);
            }
        }, 0);

        $mqtt->loop(true);

        $mqtt->disconnect();
    }
}
