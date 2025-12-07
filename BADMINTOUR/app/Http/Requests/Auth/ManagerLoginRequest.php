<?php

namespace App\Http\Requests\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ManagerLoginRequest extends LoginRequest
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

        // Verify the user is a manager
        $user = Auth::user();
        if (!$user || $user->role !== 'manager') {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'This account is not a Manager account. Please use the Player login page.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }
}

