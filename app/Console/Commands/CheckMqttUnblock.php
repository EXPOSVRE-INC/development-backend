<?php

namespace App\Console\Commands;

use App\Models\Block;
use Illuminate\Support\Facades\Log;
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

        // Listen to block topic
        $mqtt->subscribe('user/block/#', function ($topic, $message) {
            $message = json_decode($message);

            if ($this->isBlocked($message->from, $message->to)) {
                $this->handleBlockedUsers($message->from, $message->to);
                return; // Skip further processing
            }
        }, 0);

        // Listen to unblock topic
        $mqtt->subscribe('user/unblock/#', function ($topic, $message) {
            $message = json_decode($message);

            if (!$this->isBlocked($message->from, $message->to)) {
                $this->handleUnblockedUsers($message->from, $message->to);
                return; // Skip further processing
            }
        }, 0);

        $mqtt->loop(true);
        $mqtt->disconnect();
    }

    // Check if one user has blocked the other
    protected function isBlocked($userFrom, $userTo)
    {
        $blockExists = Block::where(function ($query) use ($userFrom, $userTo) {
            $query->where('user_id', $userFrom)->where('blocking_id', $userTo);
        })->orWhere(function ($query) use ($userFrom, $userTo) {
            $query->where('user_id', $userTo)->where('blocking_id', $userFrom);
        })->exists();

        return $blockExists;
    }

    // Handle logic for when users are blocked
    protected function handleBlockedUsers($userFrom, $userTo)
    {
        Log::info('Handling block event...');
        $mqtt = MQTT::connection();

        // Notify sender that chat is hidden
        $senderMessage = json_encode([
            'action' => 'block',
            'message' => 'You have blocked the user. The conversation has been hidden.',
            'user_id' => $userTo
        ]);
        // $mqtt->publish("chat/{$userFrom}/update", $senderMessage);
        Log::info("Published to sender chat/{$userFrom}/update: " . $senderMessage);

        // Notify receiver that chat is hidden
        $receiverMessage = json_encode([
            'action' => 'block',
            'message' => 'This conversation has been hidden due to a block.',
            'user_id' => $userFrom
        ]);
        // $mqtt->publish("chat/{$userTo}/update", $receiverMessage);
        Log::info("Published to receiver chat/{$userTo}/update: " . $receiverMessage);
    }

    protected function handleUnblockedUsers($userFrom, $userTo)
    {
        Log::info('Handling unblock event...');
        $mqtt = MQTT::connection();

        $senderMessage = json_encode([
            'action' => 'unblock',
            'message' => 'You have unblocked the user. The conversation is now visible.',
            'user_id' => $userTo
        ]);
        // $mqtt->publish("chat/{$userFrom}/update", $senderMessage);
        Log::info("Published unblock to sender chat/{$userFrom}/update: " . $senderMessage);

        $receiverMessage = json_encode([
            'action' => 'unblock',
            'message' => 'This conversation is now visible due to an unblock.',
            'user_id' => $userFrom
        ]);
        // $mqtt->publish("chat/{$userTo}/update", $receiverMessage);
        Log::info("Published unblock to receiver chat/{$userTo}/update: " . $receiverMessage);
    }
}
