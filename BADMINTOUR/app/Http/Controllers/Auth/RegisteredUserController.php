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
            'password' => ['required', 'confirmed', 'string', 'min:8'],
            'password_confirmation' => ['required', 'string'],
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

        // Build full name from parts
        $fullName = trim($request->first_name . ' ' . ($request->middle_name ?? '') . ' ' . $request->last_name);

        // Prepare user data
        $userData = [
            'name' => $fullName,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'contact_number' => $request->contact_number,
        ];

        // Add player-specific fields
        if ($request->role === 'player') {
            $userData = array_merge($userData, [
                'birth_month' => $request->birth_month,
                'birth_day' => $request->birth_day,
                'birth_year' => $request->birth_year,
                'gender' => $request->gender,
                'height' => $request->height,
                'weight' => $request->weight,
                'region' => $request->region,
                'province' => ($request->province === 'N/A' || empty($request->province)) ? null : $request->province,
                'city' => $request->city,
                'biodata_completed' => false, // New players must complete biodata
            ]);
        } elseif ($request->role === 'manager') {
            // Handle manager ID document upload - store in PRIVATE storage for security
            if ($request->hasFile('id_document')) {
                $file = $request->file('id_document');
                $fileName = time() . '_' . $file->getClientOriginalName();
                // Store in private disk (storage/app/manager_ids) - NOT publicly accessible
                $filePath = $file->storeAs('manager_ids', $fileName);
                $userData['id_document'] = $filePath;
                // Auto-approve managers immediately (can add third-party verification later)
                $userData['verification_status'] = 'verified';
            }
        }

        $user = User::create($userData);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
