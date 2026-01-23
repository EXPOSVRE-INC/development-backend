<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\Models\Notification as NotificationModel;
use Illuminate\Support\Facades\Log;

class CustomDatabaseChannel
{
    public function send($notifiable, Notification $notification)
    {
        $data = $notification->toDatabase($notifiable);
        try {
            NotificationModel::create([
                'type' => $data['type'],
                'user_id' => $data['user_id'],
                'sender_id' => $data['sender_id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'post_id' => $data['post_id'] ?? null,
                'deep_link' => $data['deep_link'],
            ]);
        } catch (\Exception $e) {
            Log::error('CustomDatabaseChannel - Failed to create notification: ' . $e->getMessage());
            throw $e;
        }
    }
}
