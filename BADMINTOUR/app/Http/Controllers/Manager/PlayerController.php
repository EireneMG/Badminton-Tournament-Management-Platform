<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\ClubPlayer;
use Illuminate\View\View;

class PlayerController extends Controller
{
    /**
     * Display the players list page (all players in the system).
     */
    public function index(): View
    {
        $user = auth()->user();
        $club = Club::where('manager_id', $user->id)->first();
        
        // Get ALL players in the system (all registered players)
        $allPlayers = \App\Models\User::where('role', 'player')
            ->with(['approvedClubMembership.club', 'clubMemberships.club', 'eloRatings'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('manager.players', compact('club', 'allPlayers'));
    }
}
