<?php

namespace App\Console\Commands;

use App\Models\Block;
use App\Models\User;
use App\Notifications\MessageNewNotification;
use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;

class CheckMqttMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:chat';

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

        $mqtt->subscribe('chat/#', function ($topic, $message) {
//            dump($topic);
            $message = json_decode($message);
            if ($message->received == false) {
                $userFrom = User::where(['id' => $message->from])->first();
                $userTo = User::where(['id' => $message->to])->first();
                $deepLink = 'EXPOSVRE://user/'. $userFrom->id;

                $notification = new \App\Models\Notification();
                $notification->title = 'You have new message from,' . $userFrom->username;
                $notification->description = $userFrom->username . ':' . $message->message;
                $notification->type = 'newmessage';
                $notification->user_id = $userTo->id;
                $notification->sender_id = $userFrom->id;
                $notification->deep_link = $deepLink;
                $notification->save();

                $userTo->notify(new MessageNewNotification($userFrom, $userTo, $message->message));

            }
        }, 0);

        $mqtt->loop(true);

        $mqtt->disconnect();
    }

}
