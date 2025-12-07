<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Services\EmailService;

class ForgotEmailController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Display the forgot email form.
     */
    public function create(): View
    {
        return view('auth.forgot-email');
    }

    /**
     * Handle the forgot email request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Rate limiting
        $key = 'forgot-email:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => 'Too many attempts. Please try again in ' . ceil($seconds / 60) . ' minute(s).',
            ]);
        }

        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:20'],
        ]);

        // Find user matching criteria
        $user = User::where('first_name', $request->first_name)
            ->where('last_name', $request->last_name)
            ->where('contact_number', $request->contact_number)
            ->first();

        if (!$user) {
            RateLimiter::hit($key);
            return back()->withErrors([
                'email' => 'No account found matching the provided information. Please check your details and try again.',
            ])->withInput();
        }

        RateLimiter::clear($key);

        // Mask email for display
        $emailParts = explode('@', $user->email);
        $maskedEmail = substr($emailParts[0], 0, 1) . '***' . '@' . $emailParts[1];

        // Send email notification (or log for testing)
        $this->emailService->sendForgotEmail($user);

        return back()->with([
            'status' => 'success',
            'masked_email' => $maskedEmail,
            'full_email' => $user->email,
        ]);
    }
}

