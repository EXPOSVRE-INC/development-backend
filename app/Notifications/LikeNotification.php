<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;
use App\Notifications\Channels\FirebaseChannel;
use Kreait\Firebase\Messaging\CloudMessage;

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
            'database',
            FirebaseChannel::class,
            ApnChannel::class,
        ];
    }


    public function toApn($notifiable)
    {

        $deepLink = 'EXPOSVRE://postlike/' . $this->post->id;
        $apnMessage = ApnMessage::create()
            ->badge(1)
            ->title('New like from ' . $this->user->username . '.')
            ->custom('deepLink', $deepLink);

        return $apnMessage;
    }

    public function toFirebase($notifiable, $token)
    {
        $deepLink = 'EXPOSVRE://postlike/' . $this->post->id;

        return CloudMessage::new()
            ->withTarget('token', $token)
            ->withNotification([
                'title' => 'New like from ' . $this->user->username,
            ])
            ->withData([
                'deepLink' => $deepLink,
            ]);
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
        return [
            'title' => 'loved your post',
            'description' => 'USER loved your post',
            'type' => 'like',
            'user_id' => $this->post->owner_id,
            'sender_id' => $this->user->id,
            'post_id' => $this->post->id,
            'deep_link' => 'EXPOSVRE://postlike/' . $this->post->id,
        ];
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
