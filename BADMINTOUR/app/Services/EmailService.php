<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ForgotEmailNotification;
use App\Mail\PasswordResetNotification;
use App\Mail\NotificationEmail;
use App\Mail\ResendDomainErrorHandler;
use App\Models\Notification;
use App\Models\User;

class EmailService
{
    /**
     * Notification types that should send email
     */
    private const EMAIL_NOTIFICATION_TYPES = [
        'club_invitation',
        'club_join_approved',
        'club_join_rejected',
        'removed_from_club',
        'tournament_published',
        'tournament_rescheduled',
        'match_scheduled',
        'match_rescheduled',
        'tournament_completed',
        'registration_approved',
        'registration_rejected',
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
                Log::info("Notification email sent successfully", [
                    'notification_id' => $notification->id,
                    'user_id' => $user->id,
                    'type' => $notification->type
                ]);
            } catch (\Exception $e) {
                // Check if it's a Resend domain limitation error
                if (ResendDomainErrorHandler::isDomainLimitationError($e)) {
                    ResendDomainErrorHandler::handleDomainError($e, $user->email, "notification ({$notification->type})");
                } else {
                    Log::error("Failed to send notification email", [
                        'notification_id' => $notification->id,
                        'user_id' => $user->id,
                        'type' => $notification->type,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
                // Don't throw - allow the application to continue even if email fails
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
            try {
                Mail::to($user->email)->send(new ForgotEmailNotification($user));
                Log::info("Forgot email notification sent successfully", [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
            } catch (\Exception $e) {
                // Check if it's a Resend domain limitation error
                if (ResendDomainErrorHandler::isDomainLimitationError($e)) {
                    ResendDomainErrorHandler::handleDomainError($e, $user->email, 'forgot email');
                    // For forgot email, we still throw but with a more helpful message
                    throw new \Exception('Email sending is currently limited. Please verify a domain in Resend to enable email functionality. Contact support for assistance.');
                }
                Log::error("Failed to send forgot email notification", [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
                throw $e; // Re-throw for forgot email as it's critical
            }
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
            try {
                Mail::to($user->email)->send(new PasswordResetNotification($user, $token));
                Log::info("Password reset email sent successfully", [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
            } catch (\Exception $e) {
                // Check if it's a Resend domain limitation error
                if (ResendDomainErrorHandler::isDomainLimitationError($e)) {
                    ResendDomainErrorHandler::handleDomainError($e, $user->email, 'password reset');
                    // For password reset, we still throw but with a more helpful message
                    throw new \Exception('Email sending is currently limited. Please verify a domain in Resend to enable password reset emails. Contact support for assistance.');
                }
                Log::error("Failed to send password reset email", [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
                throw $e; // Re-throw for password reset as it's critical
            }
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

