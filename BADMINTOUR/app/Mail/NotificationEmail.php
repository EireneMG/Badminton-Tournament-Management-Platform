<?php

namespace App\Mail;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $notification;
    public $actionUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
        $this->actionUrl = $notification->action_url 
            ? url($notification->action_url) 
            : url('/');
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject($this->notification->title)
            ->view('emails.notification')
            ->with([
                'notification' => $this->notification,
                'actionUrl' => $this->actionUrl,
            ]);
    }
}

