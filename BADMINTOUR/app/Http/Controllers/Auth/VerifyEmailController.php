<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $user = User::findOrFail($request->route('id'));

        // Validate the hash matches the user's email
        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        // Already verified
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')->with('verified', true);
        }

        // Mark verified and fire event
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Stay on verification page with verified flag (no auto-login)
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('verification.notice')->with('verified', true);
    }
}
