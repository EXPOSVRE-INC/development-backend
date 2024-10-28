<?php

namespace App\Console\Commands;

use App\Models\Block;
use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;

class CheckMqttUnblock extends Command
{
    protected $signature = 'mqtt:unblock';
    protected $description = 'Listen for chat messages and handle notifications';

    public function __construct()
    {
        parent::__construct();
    }

    // Listen to chat messages
    public function handle()
    {
        $mqtt = MQTT::connection();

        $mqtt->subscribe('user/block/#', function ($topic, $message) {
            $message = json_decode($message);

            if ($this->isBlocked($message->from, $message->to)) {
                $this->handleBlockedUsers($message->from, $message->to);
                return;
            }
        }, 0);

        $mqtt->subscribe('user/unblock/#', function ($topic, $message) {
            $message = json_decode($message);

            if (!$this->isBlocked($message->from, $message->to)) {
                $this->handleUnblockedUsers($message->from, $message->to);
                return;
            }
        }, 0);

        $mqtt->loop(true);
        $mqtt->disconnect();
    }

    protected function isBlocked($userFrom, $userTo)
    {
        $blockExists = Block::where(function ($query) use ($userFrom, $userTo) {
            $query->where('user_id', $userFrom)->where('blocking_id', $userTo);
        })->orWhere(function ($query) use ($userFrom, $userTo) {
            $query->where('user_id', $userTo)->where('blocking_id', $userFrom);
        })->exists();

        return $blockExists;
    }

    protected function handleBlockedUsers($userFrom, $userTo)
    {
        $mqtt = MQTT::connection();

        // Notify sender that chat is hidden
        $senderMessage = json_encode([
            'action' => 'block',
            'message' => 'You have blocked the user. The conversation has been hidden.',
            'user_id' => $userTo
        ]);

        // Notify receiver that chat is hidden
        $receiverMessage = json_encode([
            'action' => 'block',
            'message' => 'This conversation has been hidden due to a block.',
            'user_id' => $userFrom
        ]);

    }

    protected function handleUnblockedUsers($userFrom, $userTo)
    {
        $mqtt = MQTT::connection();

        $senderMessage = json_encode([
            'action' => 'unblock',
            'message' => 'You have unblocked the user. The conversation is now visible.',
            'user_id' => $userTo
        ]);

        $receiverMessage = json_encode([
            'action' => 'unblock',
            'message' => 'This conversation is now visible due to an unblock.',
            'user_id' => $userFrom
        ]);
    }
}
