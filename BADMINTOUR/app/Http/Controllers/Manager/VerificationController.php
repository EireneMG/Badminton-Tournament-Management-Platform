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

        // Handle file upload
        $idFilePath = $request->file('id_file')->store('manager-ids', 'public');

        // Create or update verification record and auto-verify upon upload
        ManagerIdVerification::updateOrCreate(
            ['manager_id' => $user->id],
            [
                'id_type' => $request->id_type,
                'id_file_path' => $idFilePath,
                'status' => 'verified',
                'submitted_at' => now(),
            ]
        );

        // Also mark user verification_status as verified for consistency
        $user->update(['verification_status' => 'verified']);

        return redirect()->route('manager.create-club')
            ->with('success', 'ID uploaded and verified automatically. Please complete your club registration.');
    }
}
