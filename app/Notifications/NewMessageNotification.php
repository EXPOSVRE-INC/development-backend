<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;

class NewMessageNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */

    private $subscriber;
    private $user;
    private $message;

    public function __construct($user, $subscriber, $message)
    {
        $this->user = $user;
        $this->subscriber = $subscriber;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return [
//            'database',
            ApnChannel::class
        ];
    }


    public function toApn($notifiable)
    {
        $deepLink = 'EXPOSVRE://user/'. $this->subscriber->id;
        $notification = new \App\Models\Notification();
        $notification->title = 'You have new message from,' . $this->subscriber->username;
        $notification->description = 'You have new message from,' . $this->subscriber->username;
        $notification->type = 'newmessage';
        $notification->user_id = $this->user->id;
        $notification->sender_id = $this->subscriber->id;
        $notification->deep_link = $deepLink;
        $notification->save();

        $apnMessage = ApnMessage::create()
            ->badge(1)
            ->title('You have new message from, ' . $this->subscriber->username . '.')
            ->body($this->message)
            ->custom('deepLink', $deepLink);

        return $apnMessage;
    }

    public function toDatabase($notifiable)
    {
        $deepLink = 'EXPOSVRE://user/'. $this->subscriber->id;
        $notification = new \App\Models\Notification();
        $notification->title = 'You have new message from,' . $this->subscriber->username;
        $notification->description = 'You have new message from,' . $this->subscriber->username;
        $notification->type = 'newmessage';
        $notification->user_id = $this->user->id;
        $notification->sender_id = $this->subscriber->id;
        $notification->deep_link = $deepLink;
        $notification->save();

        return $notification;
    }

}
