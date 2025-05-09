<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;

class LikeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $user;
    private $post;

    public function __construct($user, $post)
    {
        $this->user = $user;
        $this->post = $post;
    }

    public function via($notifiable)
    {
        return [
            //            'database',
            //            'mail',
            ApnChannel::class
        ];
    }


    public function toApn($notifiable)
    {
        //        dump($this);
        $deepLink = 'EXPOSVRE://postlike/' . $this->post->id;
        //
        //        $notification = new \App\Models\Notification();
        //        $notification->title = 'liked your post';
        //        $notification->description = 'like on your post';
        //        $notification->type = 'like';
        //        $notification->user_id = $this->post->owner_id;
        //        $notification->sender_id = $this->user->id;
        //        $notification->post_id = $this->post->id;
        //        $notification->deep_link = $deepLink;
        //        $notification->save();

        //        dump($notification);

        $apnMessage = ApnMessage::create()
            ->badge(1)
            ->title('New like from ' . $this->user->username . '.')
            ->custom('deepLink', $deepLink);

        return $apnMessage;
    }

    public function routeNotificationForApn($notifiable)
    {
        dump($notifiable->token);
        Log::debug('MyNotification routeNotificationForApn called');
        return $notifiable->token;
        // return $notifiable->pushToken;

    }

    public function toDatabase($notifiable)
    {
        dump($this);
        $deepLink = 'EXPOSVRE://postlike/' . $this->post->id;

        $notification = new \App\Models\Notification();
        $notification->title = 'loved your post';
        $notification->description = 'USER loved your post';
        $notification->type = 'like';
        $notification->user_id = $this->post->owner_id;
        $notification->sender_id = $this->user->id;
        $notification->post_id = $this->post->id;
        $notification->deep_link = $deepLink;
        $notification->save();


        return $notification;
    }
    public function toMail($notifiable)
    {
        dump($this);
        $deepLink = 'EXPOSVRE://postlike/' . $this->post->id;

        $notification = new \App\Models\Notification();
        $notification->title = 'loved your post';
        $notification->description = 'loved your post';
        $notification->type = 'like';
        $notification->user_id = $this->post->owner_id;
        $notification->sender_id = $this->user->id;
        $notification->post_id = $this->post->id;
        $notification->deep_link = $deepLink;
        $notification->save();


        //        return '';
        return (new MailMessage);
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
