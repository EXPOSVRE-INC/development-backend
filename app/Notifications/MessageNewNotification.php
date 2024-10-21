<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Apn\ApnChannel;
use NotificationChannels\Apn\ApnMessage;

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
//            'database',
            ApnChannel::class
        ];
    }

    public function toApn($notifiable)
    {
        $deepLink = 'EXPOSVRE://user/'. $this->sender->id;

//        $notification = new \App\Models\Notification();
//        $notification->title = 'You have new message from,' . $this->sender->username;
//        $notification->description = $this->sender->username . ':' . $this->message;
//        $notification->type = 'newmessage';
//        $notification->user_id = $this->receiver->id;
//        $notification->sender_id = $this->sender->id;
////        $notification->post_id = $this->collection->id;
//        $notification->deep_link = $deepLink;
//        $notification->save();

        return ApnMessage::create()
            ->badge(1)
            ->title('You have new message from, ' . $this->sender->profile->firsName . ' ' . $this->sender->profile->firsName . '.')
            ->body($this->message)
            ->custom('deepLink', $deepLink);
    }

    public function toDatabase($notifiable)
    {
//         $deepLink = 'EXPOSVRE://user/'. $this->sender->id;

//         $notification = new \App\Models\Notification();
//         $notification->title = 'You have new message from,' . $this->sender->username;
//         $notification->description = $this->sender->username . ':' . $this->message;
//         $notification->type = 'newmessage';
//         $notification->user_id = $this->receiver->id;
//         $notification->sender_id = $this->sender->id;
// //        $notification->post_id = $this->collection->id;
//         $notification->deep_link = $deepLink;
//         $notification->save();

//         return $notification;
    }
}
