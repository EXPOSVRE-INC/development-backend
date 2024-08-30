<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;

class NewCommentForUser extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $user;
    private $comment;
    private $comentedUser;

    public function __construct($userWhoComment, $comment, $comentedUser)
    {
        $this->user = $userWhoComment;
        $this->comment = $comment;
        $this->comentedUser = $comentedUser;
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
        $deepLink = 'EXPOSVRE://postcomment/'. $this->post->id;

        $notification = new \App\Models\Notification();
        $notification->title = 'New Comment';
        $notification->description = 'New Comment';
        $notification->type = 'postcomment';
        $notification->user_id = $this->post->owner_id;
        $notification->sender_id = $this->user->id;
        $notification->post_id = $this->post->id;
        $notification->deep_link = $deepLink;
        $notification->save();

        $apnMessage = ApnMessage::create()
            ->badge(1)
            ->title('New comment from ' . $this->user->username . '.')
            ->body($this->comment)
            ->custom('deepLink', 'EXPOSVRE://user/'. $this->comentedUser->id);

        return $apnMessage;
    }

    public function toDatabase($notifiable)
    {
        $deepLink = 'EXPOSVRE://postcomment/'. $this->post->id;

        $notification = new \App\Models\Notification();
        $notification->title = 'New Comment';
        $notification->description = 'New Comment';
        $notification->type = 'postcomment';
        $notification->user_id = $this->post->owner_id;
        $notification->sender_id = $this->user->id;
        $notification->post_id = $this->post->id;
        $notification->deep_link = $deepLink;
        $notification->save();

        return $notification;
    }
}
