<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;
use App\Notifications\Channels\FirebaseChannel;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Notifications\Channels\CustomDatabaseChannel;

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
            CustomDatabaseChannel::class,
            FirebaseChannel::class,
            ApnChannel::class,
        ];
    }


    public function toApn($notifiable)
    {
        return ApnMessage::create()
            ->badge(1)
            ->title('Price request accepted')
            ->body('Hello, the price of this is ' . number_format($this->post->fixed_price, 2) . '$')
            ->custom('deepLink', 'EXPOSVRE://notifications');
    }

    public function toDatabase($notifiable)
    {
        $priceToShow = $this->request->offered_price
            ?? $this->request->post->fixed_price;

        $priceLabel = $this->request->offered_price
            ? 'Hello, your offer was accepted'
            : 'Hello, the price of this is';

        return [
            'title' => "{$priceLabel}: $" . number_format($priceToShow, 2),
            'description' => (string) $this->request->id,
            'type' => 'priceRespondedApprove',
            'user_id' => $notifiable->id,
            'sender_id' => $this->post->owner_id,
            'post_id' => $this->post->id,
            'deep_link' => 'EXPOSVRE://post/' . $this->post->id,
        ];
    }

    public function toFirebase($notifiable, $token)
    {
        return CloudMessage::new()
            ->withTarget('token', $token)
            ->withNotification([
                'title' => 'Price request accepted',
                'body' => 'Hello, the price of this is ' . number_format($this->post->fixed_price, 2) . '$'
            ])
            ->withData([
                'deepLink' => 'EXPOSVRE://notifications',
            ]);
    }
}
