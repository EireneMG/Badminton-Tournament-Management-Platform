<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\StoreIdVerificationRequest;
use App\Models\ManagerIdVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VerificationController extends Controller
{
    /**
     * Display the ID verification form.
     */
    public function create(): View
    {
        return view('manager.verify-id');
    }

    /**
     * Handle the ID verification upload.
     */
    public function store(StoreIdVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Check if manager already has a verification record
        $existingVerification = $user->managerIdVerification;

        if ($existingVerification && $existingVerification->status === 'submitted') {
            return back()->with('error', 'You have already submitted your ID verification.');
        }

        // Handle file upload
        $idFilePath = $request->file('id_file')->store('manager-ids', 'public');

        // Create or update verification record
        ManagerIdVerification::updateOrCreate(
            ['manager_id' => $user->id],
            [
                'id_type' => $request->id_type,
                'id_file_path' => $idFilePath,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]
        );

        return redirect()->route('manager.create-club')
            ->with('success', 'ID verification submitted successfully! Please complete your club registration.');
    }
}
