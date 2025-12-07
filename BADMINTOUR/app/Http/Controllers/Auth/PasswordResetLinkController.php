<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Find user first
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'We can\'t find a user with that email address.']);
        }
        
        // If email is disabled, handle token creation manually for testing
        if (!config('mail.enabled', false)) {
            // Create token manually
            $token = Password::getRepository()->create($user);
            $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);
            
            // Log to email simulation
            Log::channel('email-simulation')->info('Password Reset Link', [
                'email' => $user->email,
                'reset_url' => $resetUrl,
                'timestamp' => now(),
            ]);
            
            // Store reset link in session for testing display
            session()->flash('reset_link', $resetUrl);
            
            return back()->with('status', 'Password reset link has been generated. Check the message below for the reset link.');
        }
        
        // Email is enabled - use Laravel's default behavior
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', 'We have emailed your password reset link.');
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => 'We can\'t find a user with that email address.']);
    }
}
