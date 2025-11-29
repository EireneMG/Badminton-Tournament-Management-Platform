<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Models\EloRating;
use App\Models\TournamentMatch;
use App\Models\Club;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): View
    {
        $player = auth()->user();
        
        $upcomingTournaments = Tournament::where('start_date', '>', Carbon::now())
            ->where('registration_deadline', '>', Carbon::now())
            ->orderBy('start_date', 'asc')
            ->limit(3)
            ->get();
        
        $playerRegistrations = TournamentRegistration::where('player_id', $player->id)
            ->whereIn('status', ['pending', 'eligible', 'awaiting_payment', 'paid', 'approved'])
            ->with(['tournament', 'category'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        $upcomingMatches = TournamentMatch::where(function ($query) use ($player) {
                $query->where('player1_id', $player->id)
                    ->orWhere('player2_id', $player->id)
                    ->orWhere('player1_partner_id', $player->id)
                    ->orWhere('player2_partner_id', $player->id);
            })
            ->whereNull('winner_id')
            ->where('scheduled_date', '>=', Carbon::today())
            ->with(['tournament', 'category', 'player1', 'player2'])
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('scheduled_time', 'asc')
            ->limit(5)
            ->get();
        
        $playerRanking = EloRating::where('player_id', $player->id)
            ->orderBy('current_rating', 'desc')
            ->first();
        
        $topPlayers = EloRating::with('player.approvedClubMembership.club')
            ->orderBy('current_rating', 'desc')
            ->limit(5)
            ->get();
        
        $topClubs = Club::withCount(['approvedPlayers'])
            ->orderBy('approved_players_count', 'desc')
            ->limit(5)
            ->get();
        
        $clubMembership = $player->approvedClubMembership;
        
        return view('dashboard-player', compact(
            'player',
            'upcomingTournaments',
            'playerRegistrations',
            'upcomingMatches',
            'playerRanking',
            'topPlayers',
            'topClubs',
            'clubMembership'
        ));
    }
}
