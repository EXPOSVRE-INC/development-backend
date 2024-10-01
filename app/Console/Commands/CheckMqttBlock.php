<?php

namespace App\Console\Commands;

use App\Models\Block;
use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;

class CheckMqttBlock extends Command
{
    protected $signature = 'mqtt:block';
    protected $description = 'Listen for chat messages and handle notifications';

    public function __construct()
    {
        parent::__construct();
    }

    // Listen to chat messages
    public function handle()
    {
        //mqtt connection
        $mqtt = MQTT::connection();

        $mqtt->subscribe(
            'chat/#',
            function ($topic, $message) use ($mqtt) {
                $messageData = json_decode($message, true);

                if (!isset($messageData['from']) || !isset($messageData['to'])) {
                }

                $fromId = $messageData['from'];
                $toId = $messageData['to'];

                $isBlocked = $this->isBlocked($fromId, $toId);

                if ($isBlocked && (!isset($messageData['removed']) || $messageData['removed'] !== true)) {
                    $messageData['removed'] = true;
                    $messageData['received'] = false;

                    $updatedMessage = json_encode($messageData);

                    $mqtt->publish($topic, $updatedMessage, 0, true);

                } elseif (!$isBlocked && (isset($messageData['removed']) && $messageData['removed'] === true)) {
                    $messageData['removed'] = false;

                    $updatedMessage = json_encode($messageData);

                    $mqtt->publish($topic, $updatedMessage, 0, true);

                }
            },
            0
        );

        // $mqtt->subscribe(
        //     'user/block/#',
        //     function ($topic, $message) {
        //     },
        //     0
        // );

        // $mqtt->subscribe(
        //     'user/unblock/#',
        //     function ($topic, $message) {
        //     },
        //     0
        // );

        $mqtt->loop(true);
        $mqtt->disconnect();
    }

    protected function isBlocked($userFrom, $userTo)
    {
        return Block::where(function ($query) use ($userFrom, $userTo) {
            $query->where('user_id', $userFrom)->where('blocking_id', $userTo);
        })->orWhere(function ($query) use ($userFrom, $userTo) {
            $query->where('user_id', $userTo)->where('blocking_id', $userFrom);
        })->exists();
    }
}
