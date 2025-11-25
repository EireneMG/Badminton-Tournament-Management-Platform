<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\TournamentMatch;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MatchController extends Controller
{
    public function index(Request $request): View
    {
        $player = auth()->user();
        
        $upcomingMatches = TournamentMatch::where(function($query) use ($player) {
            $query->where('player1_id', $player->id)
                  ->orWhere('player2_id', $player->id);
        })
        ->whereIn('status', ['scheduled', 'in_progress'])
        ->with([
            'tournament', 
            'category', 
            'player1.approvedClubMembership.club', 
            'player2.approvedClubMembership.club'
        ])
        ->orderBy('scheduled_at', 'asc')
        ->get();
        
        $completedMatches = TournamentMatch::where(function($query) use ($player) {
            $query->where('player1_id', $player->id)
                  ->orWhere('player2_id', $player->id);
        })
        ->where('status', 'completed')
        ->with([
            'tournament', 
            'category', 
            'player1.approvedClubMembership.club', 
            'player2.approvedClubMembership.club', 
            'result'
        ])
        ->orderBy('updated_at', 'desc')
        ->get();
        
        $liveMatches = TournamentMatch::where(function($query) use ($player) {
            $query->where('player1_id', $player->id)
                  ->orWhere('player2_id', $player->id);
        })
        ->where('status', 'in_progress')
        ->with([
            'tournament', 
            'category', 
            'player1.approvedClubMembership.club', 
            'player2.approvedClubMembership.club'
        ])
        ->get();
        
        return view('matches.index', compact('upcomingMatches', 'completedMatches', 'liveMatches'));
    }
}
