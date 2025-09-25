<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;
use App\Notifications\Channels\FirebaseChannel;
use Kreait\Firebase\Messaging\CloudMessage;

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
        return [
            FirebaseChannel::class,
            ApnChannel::class,
        ];
    }


    public function toApn($notifiable)
    {
        $deepLink = 'EXPOSVRE://gallerycomment/' . $this->collection->id;
        return ApnMessage::create()
            ->badge(1)
            ->title('New comment from ' . $this->user->profile->firsName . ' ' . $this->user->profile->firsName . '.')
            ->body($this->comment)
            ->custom('deepLink', $deepLink);
    }

    public function toFirebase($notifiable, $token)
    {
        $deepLink = 'EXPOSVRE://gallerycomment/' . $this->collection->id;

        return CloudMessage::new()
            ->withTarget('token', $token)
            ->withNotification([
                'title' => 'New comment from ' . $this->user->profile->firsName . ' ' . $this->user->profile->firsName . '.',
                'body' => $this->comment,
            ])
            ->withData([
                'deepLink' => $deepLink,
            ]);
    }
}
