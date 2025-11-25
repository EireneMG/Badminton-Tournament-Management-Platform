<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\EloRating;
use App\Models\MatchResult;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class RankingController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->get('category', "Men's Singles");
        
        $eloRatings = EloRating::with(['player.approvedClubMembership.club'])
            ->where('category', $category)
            ->orderBy('current_rating', 'desc')
            ->get();
        
        $playerIds = $eloRatings->pluck('player_id')->toArray();
        
        $winCounts = MatchResult::select('winner_id', DB::raw('count(*) as wins'))
            ->whereIn('winner_id', $playerIds)
            ->groupBy('winner_id')
            ->pluck('wins', 'winner_id')
            ->toArray();
        
        $rankings = $eloRatings->map(function ($ranking, $index) use ($winCounts) {
            $wins = $winCounts[$ranking->player_id] ?? 0;
            $losses = $ranking->matches_played - $wins;
            $winRate = $ranking->matches_played > 0 
                ? round(($wins / $ranking->matches_played) * 100, 1) 
                : 0;
            
            return [
                'rank' => $index + 1,
                'player' => $ranking->player,
                'club' => $ranking->player->approvedClubMembership?->club,
                'matches_played' => $ranking->matches_played,
                'wins' => $wins,
                'losses' => $losses,
                'win_rate' => $winRate,
                'current_rating' => $ranking->current_rating,
                'peak_rating' => $ranking->peak_rating,
            ];
        });
        
        $categories = [
            "Men's Singles",
            "Women's Singles",
            "Men's Doubles",
            "Women's Doubles",
            "Mixed Doubles"
        ];
        
        return view('ranking.index', compact('rankings', 'categories', 'category'));
    }
}
