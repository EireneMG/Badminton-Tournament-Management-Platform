<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Mail\ResendDomainErrorHandler;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountSettingsController extends Controller
{
    /**
     * Update the user's email address.
     */
    public function updateEmail(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'confirm_email' => ['required', 'string', 'email', 'same:new_email'],
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        // Update email with transaction for data integrity
        DB::transaction(function() use ($user, $validated) {
            $user->email = $validated['new_email'];
            $user->email_verified_at = null; // Mark email as unverified
            $user->save();
        });

        // Send email verification notification to the new email address
        try {
            $user->sendEmailVerificationNotification();
        } catch (\Exception $e) {
            // Check if it's a Resend domain limitation error
            if (ResendDomainErrorHandler::isDomainLimitationError($e)) {
                ResendDomainErrorHandler::handleDomainError($e, $validated['new_email'], 'email verification (change email)');
                return back()->with('error', 'Email address updated, but verification email cannot be sent due to domain limitations. Please verify a domain in Resend to enable email functionality.');
            }
            
            \Illuminate\Support\Facades\Log::error('Failed to send email verification after email change', [
                'user_id' => $user->id,
                'new_email' => $validated['new_email'],
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Email address updated, but failed to send verification email. Please use the resend verification link.');
        }

        return back()->with('success', 'Email address updated successfully! A verification link has been sent to your new email address.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            'new_password_confirmation' => ['required', 'string'],
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        // Update password with transaction for data integrity
        DB::transaction(function() use ($user, $validated) {
            $user->password = Hash::make($validated['new_password']);
            $user->save();
        });

        return back()->with('success', 'Password updated successfully!');
    }
}

