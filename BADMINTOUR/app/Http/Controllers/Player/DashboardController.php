<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Models\EloRating;
use App\Models\TournamentMatch;
use App\Models\Club;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $player = auth()->user();
        
        // Redirect to profile completion if biodata is not completed
        // Note: This is a backup check - middleware should handle this, but this ensures it
        if (!$player->biodata_completed) {
            return redirect()->route('profile.edit')
                ->with('warning', 'Please complete your profile before accessing the dashboard.');
        }
        
        $upcomingTournaments = Tournament::where('start_date', '>', Carbon::now())
            ->where('registration_deadline', '>', Carbon::now())
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('start_date', 'asc')
            ->limit(3)
            ->get();
        
        $playerRegistrations = TournamentRegistration::where('player_id', $player->id)
            ->whereIn('status', ['pending', 'eligible', 'approved'])
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
        
        // Top players by highest rating per player (dedupe across categories)
        $topPlayers = EloRating::with('player.approvedClubMembership.club')
            ->select('player_id', DB::raw('MAX(current_rating) as current_rating'))
            ->groupBy('player_id')
            ->orderByDesc('current_rating')
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
