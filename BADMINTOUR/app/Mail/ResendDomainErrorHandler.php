<?php

namespace App\Mail;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportException;

class ResendDomainErrorHandler
{
    /**
     * Check if the exception is a Resend domain limitation error
     */
    public static function isDomainLimitationError(\Exception $e): bool
    {
        $message = $e->getMessage();
        
        // Check for Resend domain limitation error messages
        return str_contains($message, 'You can only send testing emails to your own email address') ||
               str_contains($message, 'please verify a domain at resend.com/domains');
    }

    /**
     * Handle Resend domain limitation errors gracefully
     */
    public static function handleDomainError(\Exception $e, string $recipientEmail, string $emailType = 'email'): void
    {
        $errorMessage = "Resend domain limitation: Cannot send {$emailType} to {$recipientEmail}. ";
        $errorMessage .= "Domain verification required. See: https://resend.com/domains";
        
        Log::error($errorMessage, [
            'recipient' => $recipientEmail,
            'email_type' => $emailType,
            'original_error' => $e->getMessage(),
            'solution' => 'Verify a domain in Resend dashboard and update MAIL_FROM_ADDRESS in .env'
        ]);
    }

    /**
     * Get user-friendly error message for domain limitation
     */
    public static function getUserFriendlyMessage(): string
    {
        return 'Email sending is currently limited. Please verify a domain in Resend to enable full email functionality.';
    }
}

