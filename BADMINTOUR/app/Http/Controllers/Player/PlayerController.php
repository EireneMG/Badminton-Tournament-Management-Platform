<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PlayerController extends Controller
{
    /**
     * Display the players list page (all players in the system).
     */
    public function index(): View
    {
        // Get ALL players in the system (all registered players)
        $allPlayers = \App\Models\User::where('role', 'player')
            ->with(['approvedClubMembership.club', 'clubMemberships.club', 'eloRatings'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('players.index', compact('allPlayers'));
    }
}

