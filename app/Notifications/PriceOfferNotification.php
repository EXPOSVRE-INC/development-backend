<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;
use App\Notifications\Channels\FirebaseChannel;
use Kreait\Firebase\Messaging\CloudMessage;

class PriceOfferNotification  extends Notification
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
            ->title('Offered Price request')
            ->body($this->requestor->profile->firstName . ' ' . $this->requestor->profile->lastName . ' offered $' . $this->requestor->offered_price)
            ->custom('deepLink', 'EXPOSVRE://notifications');
    }

    public function toDatabase($notifiable)
    {
        //        dump($this->requestor->profile);
        $notification = new \App\Models\Notification();
        $notification->title = $this->requestor->profile->firstName . ' ' . $this->requestor->profile->lastName . ' is interested in item';
        $notification->description = 'interested in item';
        $notification->type = 'priceRequest';
        $notification->user_id = $this->post->owner_id;
        $notification->sender_id = $this->requestor->id;
        $notification->post_id = $this->post->id;
        $notification->deep_link = '';
        $notification->save();

        $deepLink = 'EXPOSVRE://request/' . $this->post->id . '/' . $this->request->id;
        $notification->deep_link = $deepLink;
        $notification->save();


        return $notification;
    }

    public function toFirebase($notifiable, $token)
    {
        return CloudMessage::new()
            ->withTarget('token', $token)
            ->withNotification([
                'title' => 'Offered Price request',
                'body' => $this->requestor->profile->firstName . ' ' . $this->requestor->profile->lastName . ' offered $' . $this->requestor->offered_price
            ])
            ->withData([
                'deepLink' => 'EXPOSVRE://notifications',
            ]);
    }
}
