<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TournamentMatch;
use App\Models\TournamentRegistration;
use App\Models\RankingHistory;
use App\Models\MatchResult;
use App\Models\EloRating;
use Illuminate\View\View;
use Carbon\Carbon;

class ProfileController extends Controller
{
    public function show(): View
    {
        $player = auth()->user();
        
        $clubMembership = $player->approvedClubMembership;
        $club = $clubMembership ? $clubMembership->club : null;
        
        // Calculate age from birth_month, birth_day, birth_year fields
        $age = null;
        if ($player->birth_year && $player->birth_month && $player->birth_day) {
            $birthDate = Carbon::createFromDate($player->birth_year, $player->birth_month, $player->birth_day);
            $age = $birthDate->age;
        }
        
        $matches = TournamentMatch::where(function($query) use ($player) {
            $query->where('player1_id', $player->id)
                  ->orWhere('player2_id', $player->id);
        })->with(['result', 'tournament', 'category'])->get();
        
        $totalMatches = $matches->count();
        $wins = MatchResult::where('winner_id', $player->id)->count();
        $losses = $totalMatches - $wins;
        $winRate = $totalMatches > 0 ? round(($wins / $totalMatches) * 100, 1) : 0;
        
        $eloRatings = EloRating::where('player_id', $player->id)->get();
        
        $registrations = TournamentRegistration::where('player_id', $player->id)
            ->with(['tournament', 'category'])
            ->latest()
            ->take(10)
            ->get();
        
        $rankingHistory = RankingHistory::where('player_id', $player->id)
            ->with('tournament')
            ->orderBy('recorded_at', 'desc')
            ->take(20)
            ->get();
        
        $recentMatches = $matches->filter(function($match) {
            return $match->status === 'completed' && $match->result;
        })->sortByDesc('updated_at')->take(10);
        
        return view('players.show', compact(
            'player',
            'club',
            'age',
            'totalMatches',
            'wins',
            'losses',
            'winRate',
            'eloRatings',
            'registrations',
            'rankingHistory',
            'recentMatches'
        ));
    }
    
    public function showOther(User $user): View
    {
        $player = $user;
        
        $clubMembership = $player->approvedClubMembership;
        $club = $clubMembership ? $clubMembership->club : null;
        
        // Calculate age from birth_month, birth_day, birth_year fields
        $age = null;
        if ($player->birth_year && $player->birth_month && $player->birth_day) {
            $birthDate = Carbon::createFromDate($player->birth_year, $player->birth_month, $player->birth_day);
            $age = $birthDate->age;
        }
        
        $matches = TournamentMatch::where(function($query) use ($player) {
            $query->where('player1_id', $player->id)
                  ->orWhere('player2_id', $player->id);
        })->with(['result', 'tournament', 'category'])->get();
        
        $totalMatches = $matches->count();
        $wins = MatchResult::where('winner_id', $player->id)->count();
        $losses = $totalMatches - $wins;
        $winRate = $totalMatches > 0 ? round(($wins / $totalMatches) * 100, 1) : 0;
        
        $eloRatings = EloRating::where('player_id', $player->id)->get();
        
        $registrations = TournamentRegistration::where('player_id', $player->id)
            ->with(['tournament', 'category'])
            ->latest()
            ->take(10)
            ->get();
        
        $rankingHistory = RankingHistory::where('player_id', $player->id)
            ->with('tournament')
            ->orderBy('recorded_at', 'desc')
            ->take(20)
            ->get();
        
        $recentMatches = $matches->filter(function($match) {
            return $match->status === 'completed' && $match->result;
        })->sortByDesc('updated_at')->take(10);
        
        return view('players.show', compact(
            'player',
            'club',
            'age',
            'totalMatches',
            'wins',
            'losses',
            'winRate',
            'eloRatings',
            'registrations',
            'rankingHistory',
            'recentMatches'
        ));
    }
}
