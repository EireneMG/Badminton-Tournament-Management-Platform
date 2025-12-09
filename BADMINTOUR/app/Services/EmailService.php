<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ForgotEmailNotification;
use App\Mail\PasswordResetNotification;
use App\Mail\NotificationEmail;
use App\Models\Notification;
use App\Models\User;

class EmailService
{
    /**
     * Notification types that should send email
     */
    private const EMAIL_NOTIFICATION_TYPES = [
        // Club interactions
        'club_invitation',
        'club_join_approved',
        'club_join_rejected',
        'removed_from_club',
        
        // Tournament events
        'tournament_published',
        'match_scheduled',
        'match_rescheduled',
        'tournament_completed',
        
        // Registration status
        'registration_approved',
        'registration_rejected',
        'registration_awaiting_payment',
        
        // Withdrawal
        'withdrawal_approved',
        'withdrawal_rejected',
    ];

    /**
     * Check if email is enabled in config
     */
    public function isEnabled(): bool
    {
        return config('mail.enabled', false);
    }

    /**
     * Check if notification type should send email
     */
    public function shouldSendEmail(string $notificationType): bool
    {
        return in_array($notificationType, self::EMAIL_NOTIFICATION_TYPES);
    }

    /**
     * Send notification email
     */
    public function sendNotificationEmail(Notification $notification): void
    {
        if (!$this->shouldSendEmail($notification->type)) {
            return; // Don't send email for this notification type
        }

        $user = $notification->user;
        if (!$user || !$user->email) {
            return; // No user or email address
        }

        if ($this->isEnabled()) {
            try {
                Mail::to($user->email)->send(new NotificationEmail($notification));
            } catch (\Exception $e) {
                Log::error("Failed to send notification email", [
                    'notification_id' => $notification->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            $this->logEmail('notification', $user->email, [
                'subject' => $notification->title,
                'message' => $notification->message,
                'type' => $notification->type,
                'action_url' => $notification->action_url,
            ]);
        }
    }

    /**
     * Send forgot email notification
     */
    public function sendForgotEmail($user): void
    {
        if ($this->isEnabled()) {
            Mail::to($user->email)->send(new ForgotEmailNotification($user));
        } else {
            $this->logEmail('forgot_email', $user->email, [
                'subject' => 'Your Email Address',
                'content' => 'Your email: ' . $user->email
            ]);
        }
    }

    /**
     * Send password reset notification
     */
    public function sendPasswordReset($user, $token): void
    {
        if ($this->isEnabled()) {
            Mail::to($user->email)->send(new PasswordResetNotification($user, $token));
        } else {
            $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);
            $this->logEmail('password_reset', $user->email, [
                'subject' => 'Reset Your Password',
                'reset_url' => $resetUrl
            ]);
        }
    }

    /**
     * Log email to file for testing
     */
    private function logEmail(string $type, string $to, array $data): void
    {
        Log::channel('email-simulation')->info("Email: {$type}", [
            'to' => $to,
            'timestamp' => now(),
            ...$data
        ]);
    }
}

