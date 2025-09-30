<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;
use App\Notifications\Channels\FirebaseChannel;
use Kreait\Firebase\Messaging\CloudMessage;

class MessageNewNotification extends Notification
{
    use Queueable;

    private $sender;
    private $receiver;
    private $message;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($sender, $receiver, $message)
    {
        $this->sender = $sender;
        $this->receiver = $receiver;
        $this->message = $message;
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
        $deepLink = 'EXPOSVRE://user/' . $this->sender->id;

        return ApnMessage::create()
            ->badge(1)
            ->title('You have new message from, ' . $this->sender->profile->firstName . ' ' . $this->sender->profile->lastName . '.')
            ->body($this->message)
            ->custom('deepLink', $deepLink);
    }

    public function toFirebase($notifiable, $token)
    {
        $deepLink = 'EXPOSVRE://user/' . $this->sender->id;

        return CloudMessage::new()
            ->withTarget('token', $token)
            ->withNotification([
                'title' => 'You have new message from,' . $this->sender->profile->firstName . ' ' . $this->sender->profile->lastName . '.',
                'body'  => $this->message,
            ])
            ->withData([
                'deepLink' => $deepLink,
            ]);
    }

    public function toDatabase($notifiable) {}
}
