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

class NewCommentForPost extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $user;
    private $comment;
    private $post;

    public function __construct($user, $comment, $post)
    {
        $this->user = $user;
        $this->comment = $comment;
        $this->post = $post;
    }

    public function via($notifiable)
    {
        return [
            FirebaseChannel::class,
            ApnChannel::class,
        ];
    }


    public function toApn($notifiable)
    {
        $deepLink = 'EXPOSVRE://postcomment/' . $this->post->id;
        $apnMessage = ApnMessage::create()
            ->badge(1)
            ->title('New comment from ' . $this->user->username . '.')
            ->body($this->comment)
            ->custom('deepLink', $deepLink);

        return $apnMessage;
    }

    public function toFirebase($notifiable, $token)
    {
        $deepLink = 'EXPOSVRE://postcomment/' . $this->post->id;

        return CloudMessage::new()
            ->withTarget('token', $token)
            ->withNotification([
                'title' => 'New comment from ' . $this->user->username . '.',
                'body' => $this->comment
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
    }

    public function toDatabase($notifiable)
    {
        $deepLink = 'EXPOSVRE://postcomment/' . $this->post->id;

        $notification = new \App\Models\Notification();
        $notification->title = 'commented on your post';
        $notification->description = 'commented on your post';
        $notification->type = 'postcomment';
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
        $deepLink = 'EXPOSVRE://postcomment/' . $this->post->id;

        $notification = new \App\Models\Notification();
        $notification->title = 'commented on your post';
        $notification->description = 'commented on your post';
        $notification->type = 'postcomment';
        $notification->user_id = $this->post->owner_id;
        $notification->sender_id = $this->user->id;
        $notification->post_id = $this->post->id;
        $notification->deep_link = $deepLink;
        $notification->save();

        return (new MailMessage);
    }
}
