<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\StoreClubRequest;
use App\Models\Club;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClubController extends Controller
{
    /**
     * Display the club management page.
     */
    public function index(): View
    {
        $club = Club::where('manager_id', auth()->id())
            ->with(['players.player', 'players' => function($query) {
                $query->where('status', 'pending');
            }])
            ->first();
        
        $joinRequests = \App\Models\ClubPlayer::where('club_id', $club->id)
            ->where('status', 'pending')
            ->with('player')
            ->get();
        
        $approvedPlayers = \App\Models\ClubPlayer::where('club_id', $club->id)
            ->where('status', 'approved')
            ->with('player')
            ->get();
        
        return view('manager.club', compact('club', 'joinRequests', 'approvedPlayers'));
    }

    /**
     * Display the club creation form.
     */
    public function create(): View
    {
        // Check if manager is verified
        if (auth()->user()->verification_status !== 'verified') {
            return redirect()->route('manager.dashboard')
                ->with('error', 'Your account must be verified before you can create a club. Please wait for verification or contact support if you have questions.');
        }

        return view('manager.create-club');
    }

    /**
     * Handle the club creation.
     */
    public function store(StoreClubRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Check if manager is verified
        if ($user->verification_status !== 'verified') {
            return redirect()->route('manager.dashboard')
                ->with('error', 'Your account must be verified before you can create a club. Please wait for verification or contact support if you have questions.');
        }

        // Check if manager already has a club
        if ($user->managedClub) {
            return back()->with('error', 'You have already created a club.');
        }

        $clubData = [
            'manager_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
            'province' => $request->province,
            'city' => $request->city,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'active' => true,
        ];

        // Handle logo upload if provided
        if ($request->hasFile('logo')) {
            $clubData['logo'] = $request->file('logo')->store('club-logos', 'public');
        }

        Club::create($clubData);

        return redirect()->route('manager.dashboard')
            ->with('success', 'Club created successfully! Welcome to BadminTour.');
    }
}
