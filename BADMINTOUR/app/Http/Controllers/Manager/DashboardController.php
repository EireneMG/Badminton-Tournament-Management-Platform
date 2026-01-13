<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\User;
use App\Models\ClubPlayer;
use App\Models\EloRating;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $club = Club::where('manager_id', $user->id)->first();
        
        $ongoingTournaments = collect([]);
        $upcomingTournaments = collect([]);
        $todayMatchCount = 0;
        $topPlayers = collect([]);
        $topClubs = collect([]);
        
        if ($club) {
            $ongoingTournaments = Tournament::where('club_id', $club->id)
                ->where('status', 'ongoing')
                ->orderBy('start_date', 'asc')
                ->take(3)
                ->get();
                
            $upcomingTournaments = Tournament::where('club_id', $club->id)
                ->where('status', 'upcoming')
                ->orderBy('start_date', 'asc')
                ->take(3)
                ->get();
            
            $todayMatchCount = TournamentMatch::whereHas('tournament', function($query) use ($club) {
                $query->where('club_id', $club->id);
            })->whereDate('scheduled_date', today())->count();
        }
        
        // Top players by highest rating per player (avoid duplicates from multiple categories)
        $topPlayers = EloRating::with('player')
            ->select('player_id', DB::raw('MAX(current_rating) as current_rating'))
            ->groupBy('player_id')
            ->orderByDesc('current_rating')
            ->take(5)
            ->get();
        
        $topClubs = Club::withCount(['clubPlayers' => function($query) {
            $query->where('status', 'approved');
        }])
        ->orderBy('club_players_count', 'desc')
        ->take(3)
        ->get();

        // Manager-owned tournaments (for dashboard listing)
        $myTournaments = Tournament::where('organizer_id', $user->id)
            ->orderByDesc('created_at')
            ->take(6)
            ->get();
        
        // Hide welcome message once manager has created at least one tournament
        $hasCreatedTournament = Tournament::where('organizer_id', $user->id)->exists();
        
        return view('manager.dashboard', compact(
            'user',
            'club',
            'ongoingTournaments',
            'upcomingTournaments',
            'todayMatchCount',
            'topPlayers',
            'topClubs',
            'myTournaments',
            'hasCreatedTournament'
        ));
    }
}
