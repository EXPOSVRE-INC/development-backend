<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class UserMentioned extends Notification
{
    public $fromUser;
    public $comment;
    public $post;

    public function __construct($fromUser, $comment, $post)
    {
        $this->fromUser = $fromUser;
        $this->comment = $comment;
        $this->post = $post;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'You were mentioned in a comment',
            'description' => "{$this->fromUser->name} mentioned you in a post",
            'type' => 'mention',
            'post_id' => $this->post->id,
            'comment_id' => $this->comment->id,
        ];
    }
}
