<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\ClubPlayer;
use App\Services\StatisticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfileController extends Controller
{
    protected StatisticsService $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    public function index(): View
    {
        $user = auth()->user();
        $club = Club::where('manager_id', $user->id)->first();
        $clubMemberCount = $club 
            ? ClubPlayer::where('club_id', $club->id)->where('status', 'approved')->count() 
            : 0;

        try {
            $managerStatistics = $this->statisticsService->getManagerStatistics($user);
        } catch (\Exception $e) {
            \Log::error("Error fetching manager statistics for manager {$user->id}: " . $e->getMessage());
            $managerStatistics = [
                'tournaments_organized' => 0,
                'total_participants' => 0,
                'matches_managed' => 0,
                'average_tournament_size' => 0,
                'completion_rate' => 0,
            ];
        }

        return view('manager.profile', compact('user', 'club', 'clubMemberCount', 'managerStatistics'));
    }

    /**
     * Update the manager's profile (profile picture, name, contact number only).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $validated['profile_photo'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        // Use database transaction to ensure data integrity
        DB::transaction(function() use ($user, $validated) {
            $user->update($validated);
        });

        return redirect()->route('manager.profile')->with('success', 'Profile updated successfully!');
    }
}
