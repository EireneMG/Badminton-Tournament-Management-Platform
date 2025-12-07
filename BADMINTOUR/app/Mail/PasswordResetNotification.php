<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $token;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $this->user->email
        ]);

        return $this->subject('Reset Your Password')
            ->view('emails.password-reset')
            ->with([
                'user' => $this->user,
                'resetUrl' => $resetUrl
            ]);
    }
}

