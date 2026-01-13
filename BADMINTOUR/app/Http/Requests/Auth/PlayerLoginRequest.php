<?php

namespace App\Http\Requests\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class PlayerLoginRequest extends LoginRequest
{
    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'The email or password you entered is incorrect. Please check your credentials and try again.',
            ]);
        }

        // Verify the user is a player
        $user = Auth::user();
        if (!$user || $user->role !== 'player') {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'This account is not a Player account. Please use the Manager login page.',
            ]);
        }

        // Verify email is verified
        if (!$user->hasVerifiedEmail()) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Please verify your email address before logging in. Check your email for the verification link.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }
}

