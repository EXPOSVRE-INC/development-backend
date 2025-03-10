<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;

class PriceRequestAcceptedNotification extends Notification
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
//            'database',
            ApnChannel::class
            ];
    }


    public function toApn($notifiable)
    {
//        $deepLink = 'EXPOSVRE://post/' . $this->post->id;
//        $notification = new \App\Models\Notification();
//        $notification->title = 'Hi! The price of post is ' . round($this->post->price, 2) . '$';
//        $notification->description = $this->request->id;
//        $notification->type = 'priceResponded';
//        $notification->user_id = $this->requestor->id;
//        $notification->sender_id = $this->post->owner_id;
//        $notification->post_id = $this->post->id;
//        $notification->deep_link = $deepLink;
//        $notification->save();
//
//
//        $deepLink = 'EXPOSVRE://post/' . $this->post->id;
//        $notification->deep_link = $deepLink;
//        $notification->save();

        return ApnMessage::create()
            ->badge(1)
            ->title('Price request accepted')
            ->body('Hello, the price of this is ' . round($this->post->price, 2) . '$')
            ->custom('deepLink', 'EXPOSVRE://notifications');
    }

    public function toDatabase($notifiable)
    {
        $deepLink = 'EXPOSVRE://post/' . $this->post->id;
        $notification = new \App\Models\Notification();
        $notification->title = 'Hello, the price of this is ' . round($this->post->price, 2) . '$';
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
}
