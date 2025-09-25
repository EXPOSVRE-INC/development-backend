<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Notifications\Channels\FirebaseChannel;

class TestNotificationWithDeepLink extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [
            FirebaseChannel::class,
            ApnChannel::class,

        ];
    }

    public function toApn($notifiable)
    {
        return ApnMessage::create()
            ->badge(1)
            ->title('Test notification')
            ->body('With deepLink')
            ->custom('deepLink', 'EXPOSVRE://post/' . 2353);
    }


    public function toFirebase($notifiable, $token)
    {
        return CloudMessage::new()
            ->withTarget('token', $token)
            ->withNotification([
                'title' => 'Test notification',
                'body' => 'With deepLink',
            ])
            ->withData(['deepLink' => 'EXPOSVRE://post/2353']);
    }
}
