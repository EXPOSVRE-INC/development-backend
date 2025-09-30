<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $user;
    public $token;
    public $platform;


    public function __construct($user, $token, $platform)
    {
        $this->user = $user;
        $this->token = $token;
        $this->platform = $platform;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build($platform)
    {
        $address = env('MAIL_FROM_ADDRESS');
        $subject = 'Reset Password Notification';
        $name = 'EXPOSVRE';
        if ($platform == 'other') {
            $resetLink = url("/password/reset/{$this->token}?email={$this->user->email}");
        } else {
            $resetLink = url("/api/v.1.0/mobile/auth/reset-password/{$this->token}?email={$this->user->email}");
        }

        return $this->view('emails.reset')
            ->from($address, $name)
            ->cc($address, $name)
            ->bcc($address, $name)
            ->replyTo($address, $name)
            ->subject($subject)
            ->with([
                'link' => $resetLink,
                'user' => $this->user,
            ]);
    }
}
