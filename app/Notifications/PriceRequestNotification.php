<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Notifications\Channels\FirebaseChannel;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;

class PriceRequestNotification extends Notification
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
            'database',
            FirebaseChannel::class,
            ApnChannel::class,
        ];
    }


    public function toApn($notifiable)
    {
        return ApnMessage::create()
            ->badge(1)
            ->title('Price request')
            ->body($this->requestor->profile->firstName . ' ' . $this->requestor->profile->lastName . ' is interested in item')
            ->custom('deepLink', 'EXPOSVRE://notifications');
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->requestor->profile->firstName . ' ' . $this->requestor->profile->lastName . ' is interested in item',
            'description' => 'interested in item',
            'type' => 'priceRequest',
            'user_id' => $this->post->owner_id,
            'sender_id' => $this->requestor->id,
            'post_id' => $this->post->id,
            'deep_link' => 'EXPOSVRE://request/' . $this->post->id . '/' . $this->request->id,
        ];
    }

    public function toFirebase($notifiable, $token)
    {
        return CloudMessage::new()
            ->withTarget('token', $token)
            ->withNotification([
                'title' => 'Price request',
                'body' => $this->requestor->profile->firstName . ' ' . $this->requestor->profile->lastName . ' is interested in item'
            ])
            ->withData([
                'deepLink' => 'EXPOSVRE://notifications',
            ]);
    }
}
