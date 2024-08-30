<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;

class NewCommentForCollection extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $user;
    private $comment;
    private $collection;

    public function __construct($user, $comment, $collection)
    {
        Log::debug('MyNotification constructor called');
        $this->user = $user;
        $this->comment = $comment;
        $this->collection = $collection;
    }

    public function via($notifiable)
    {
        return [ApnChannel::class];
    }


    public function toApn($notifiable)
    {
        $deepLink = 'EXPOSVRE://gallerycomment/'. $this->collection->id;

//        $notification = new \App\Models\Notification();
//        $notification->title = 'commented on your collection';
//        $notification->description = 'commented on your collection';
//        $notification->type = 'collectioncomment';
//        $notification->user_id = $this->collection->user_id;
//        $notification->sender_id = $this->user->id;
//        $notification->post_id = $this->collection->id;
//        $notification->deep_link = $deepLink;
//        $notification->save();

        return ApnMessage::create()
            ->badge(1)
            ->title('New comment from ' . $this->user->profile->firsName . ' ' . $this->user->profile->firsName . '.')
            ->body($this->comment)
            ->custom('deepLink', $deepLink);
    }
}
