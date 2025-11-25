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
}
