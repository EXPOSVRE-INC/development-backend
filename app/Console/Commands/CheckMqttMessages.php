<?php

namespace App\Console\Commands;

use App\Models\Chat;
use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;
use Illuminate\Support\Facades\Log;


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

        $mqtt->subscribe('message/readMessage/#',  function ($topic, $message){
            $messageData = json_decode($message, true);

            $chat = Chat::where('id', $messageData['messageId'])->first();
                $userId = auth()->user()->id;
                $chats = Chat::where('created_at', '<=', $chat->created_at)
                    ->where('conversation_id', $chat->conversation_id)
                    ->where('to', $userId)
                    ->get();

            if ($messageData['readAll'] == true) {
                if ($chats) {
                    $chats->each(function ($chat) {
                        $chat->update([
                            'read' => true,
                            'received' => true,
                        ]);
                    });
                }
            } elseif ($messageData['readAll'] == false) {
                $chat = Chat::find($messageData['messageId']);
                if ($chat) {
                    $chat->update([
                        'read' => true,
                        'received' => true,
                    ]);
                }
            }

        });

        $mqtt->loop(true);

        $mqtt->disconnect();
    }
}



