<?php

namespace App\Notifications\Channels;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;

class FirebaseChannel
{
    public function send($notifiable, $notification)
    {
        if (!method_exists($notification, 'toFirebase')) {
            return;
        }

        $tokens = $notifiable->deviceTokens()
            ->where('platform', 'android')
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return;
        }

        $factory = (new Factory)
            ->withServiceAccount(storage_path('app/firebase/firebase-service-account.json'));

        $messaging = $factory->createMessaging();

        foreach ($tokens as $token) {
            $message = $notification->toFirebase($notifiable, $token);

            try {
                $messaging->send($message);
            } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
                $notifiable->deviceTokens()->where('token', $token)->delete();
            } catch (\Exception $e) {
                Log::error("Firebase notification failed for token $token: " . $e->getMessage());
            }
        }
    }
}
