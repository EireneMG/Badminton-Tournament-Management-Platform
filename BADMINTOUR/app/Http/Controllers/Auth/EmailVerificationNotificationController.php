<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResendDomainErrorHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        try {
            $request->user()->sendEmailVerificationNotification();
            return back()->with('status', 'verification-link-sent');
        } catch (\Exception $e) {
            // Check if it's a Resend domain limitation error
            if (ResendDomainErrorHandler::isDomainLimitationError($e)) {
                ResendDomainErrorHandler::handleDomainError($e, $request->user()->email, 'email verification');
                return back()->with('error', 'Email verification cannot be sent due to domain limitations. Please verify a domain in Resend to enable email functionality. Contact support for assistance.');
            }
            
            \Illuminate\Support\Facades\Log::error('Failed to send email verification notification', [
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to send verification email. Please try again or contact support if the problem persists.');
        }
    }
}
