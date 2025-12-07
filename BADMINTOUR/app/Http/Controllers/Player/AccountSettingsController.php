<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
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

        return back()->with('success', 'Email address updated successfully! Please verify your new email address.');
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

