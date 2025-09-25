<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Notifications\Channels\FirebaseChannel;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;

class PriceRequestDeclinedNotification extends Notification
{
    use Queueable;


    private $requestor;
    private $post;
    private $request;
    /**
     * Create a new notification instance.
     *
     * @return void
     */

    public function __construct($post, $requestor, $request)
    {
        $this->post = $post;
        $this->requestor = $requestor;
        $this->request = $request;
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
            ->title('Price request declined')
            //            ->body($this->comment)
            ->custom('deepLink', 'EXPOSVRE://notifications');
    }

    public function toDatabase($notifiable)
    {
        $deepLink = 'EXPOSVRE://post/' . $this->post->id;
        $notification = new \App\Models\Notification();
        $notification->title = 'Price request declined';
        $notification->description = $this->request->id;
        $notification->type = 'priceResponded';
        $notification->user_id = $this->requestor->id;
        $notification->sender_id = $this->post->owner_id;
        $notification->post_id = $this->post->id;
        $notification->deep_link = $deepLink;
        $notification->save();


        $deepLink = 'EXPOSVRE://post/' . $this->post->id;
        $notification->deep_link = $deepLink;
        $notification->save();

        return $notification;
    }

    public function toFirebase($notifiable, $token)
    {
        return CloudMessage::new()
            ->withTarget('token', $token)
            ->withNotification([
                'title' => 'Price request declined'
            ])
            ->withData([
                'deepLink' => 'EXPOSVRE://notifications',
            ]);
    }
}
