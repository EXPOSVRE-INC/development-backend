<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;
use App\Notifications\Channels\FirebaseChannel;
use Kreait\Firebase\Messaging\CloudMessage;

class NewSubscription extends Notification
{
    use Queueable;

    private $subscriber;
    private $user;
    /**
     * Create a new notification instance.
     *
     * @return void
     */

    public function __construct($user, $subscriber)
    {
        $this->user = $user;
        $this->subscriber = $subscriber;
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
            'database',
            FirebaseChannel::class,
            ApnChannel::class,
        ];
    }


    public function toApn($notifiable)
    {
        $deepLink = 'EXPOSVRE://user/' . $this->subscriber->id;
        return ApnMessage::create()
            ->badge(1)
            ->title('You have new subscriber ' . $this->subscriber->username . '.')
            //            ->body($this->comment)
            ->custom('deepLink', $deepLink);
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'started following you',
            'description' => 'started following you',
            'type' => 'subscription',
            'user_id' => $this->user->id,
            'sender_id' => $this->subscriber->id,
            'deep_link' => 'EXPOSVRE://user/' . $this->subscriber->id,
        ];
    }

    public function toFirebase($notifiable, $token)
    {
        $deepLink = 'EXPOSVRE://user/' . $this->subscriber->id;

        return CloudMessage::new()
            ->withTarget('token', $token)
            ->withNotification([
                'title' => 'You have new subscriber ' . $this->subscriber->username . '.'
            ])
            ->withData([
                'deepLink' => $deepLink,
            ]);
    }
}
