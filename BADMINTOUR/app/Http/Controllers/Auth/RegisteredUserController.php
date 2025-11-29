<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:player,manager'],
        ];

        // Additional validation for player registration
        if ($request->role === 'player') {
            $rules = array_merge($rules, [
                'first_name' => ['required', 'string', 'max:255'],
                'middle_name' => ['nullable', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'contact_number' => ['required', 'string', 'max:20'],
                'birth_month' => ['required', 'integer', 'between:1,12'],
                'birth_day' => ['required', 'integer', 'between:1,31'],
                'birth_year' => ['required', 'integer', 'min:1950', 'max:' . now()->year],
                'gender' => ['required', 'string', 'in:Male,Female'],
                'height' => ['required', 'numeric', 'min:100', 'max:250'],
                'weight' => ['required', 'numeric', 'min:30', 'max:200'],
                'region' => ['required', 'string', 'max:255'],
                'province' => ['nullable', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:255'],
            ]);
        } else {
            // Manager registration - minimal fields + ID verification
            $rules = array_merge($rules, [
                'first_name' => ['required', 'string', 'max:255'],
                'middle_name' => ['nullable', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'contact_number' => ['required', 'string', 'max:20'],
                'id_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // 5MB max
            ]);
        }

        $request->validate($rules);

        
    }
}