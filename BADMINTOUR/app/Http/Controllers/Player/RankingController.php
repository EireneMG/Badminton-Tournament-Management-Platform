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
        $category = $request->get('category', 'All');
        $division = $request->get('division', 'All'); // 'Junior', 'Senior', 'Open', or 'All'
        
        // Map category name to ELO category code
        $categoryMap = [
            "Men's Singles" => 'MS',
            "Women's Singles" => 'WS',
            "Men's Doubles" => 'MD',
            "Women's Doubles" => 'WD',
            "Mixed Doubles" => 'XD',
        ];
        
        $categories = [
            'All',
            "Men's Singles",
            "Women's Singles",
            "Men's Doubles",
            "Women's Doubles",
            "Mixed Doubles"
        ];
        
        $divisions = [
            'All' => 'All Divisions',
            'Junior' => 'Junior (Under 18)',
            'Senior' => 'Senior (18+)',
            'Open' => 'Open (All Ages)',
        ];
        
        $rankingService = app(\App\Services\RankingService::class);
        
        // Convert 'All' division to null for service call
        $divisionFilter = ($division === 'All' || $division === '') ? null : $division;
        
        if ($category === 'All') {
            // Get all players with any rating
            $allRankings = [];
            foreach ($categoryMap as $catName => $catCode) {
                $rankingsData = $rankingService->getAllRankings($catCode, $divisionFilter);
                foreach ($rankingsData as $data) {
                    if (!isset($allRankings[$data['player_id']])) {
                        $allRankings[$data['player_id']] = $data;
                    }
                }
            }
            $rankingsData = array_values($allRankings);
        } else {
            $eloCategory = $categoryMap[$category] ?? 'MS';
            $rankingsData = $rankingService->getAllRankings($eloCategory, $divisionFilter);
            
            // Filter: only show players who have actually played in this category
            // Having an ELO rating in this category already means they've played matches
            // But we also check registrations to be thorough
            $playerIds = collect($rankingsData)->pluck('player_id')->toArray();
            
            // Get players who registered for this category
            $playedInCategory = \App\Models\TournamentRegistration::whereIn('player_id', $playerIds)
                ->whereHas('category', function($query) use ($eloCategory) {
                    $query->where('name', $eloCategory);
                })
                ->where('status', 'approved')
                ->pluck('player_id')
                ->unique()
                ->toArray();
            
            // Get players who played matches in this category
            $playedInMatches = \App\Models\TournamentMatch::where(function($query) use ($playerIds) {
                    $query->whereIn('player1_id', $playerIds)
                          ->orWhereIn('player2_id', $playerIds)
                          ->orWhereIn('player1_partner_id', $playerIds)
                          ->orWhereIn('player2_partner_id', $playerIds);
                })
                ->whereHas('category', function($query) use ($eloCategory) {
                    $query->where('name', $eloCategory);
                })
                ->get()
                ->flatMap(function($match) {
                    return array_filter([
                        $match->player1_id,
                        $match->player2_id,
                        $match->player1_partner_id,
                        $match->player2_partner_id
                    ]);
                })
                ->unique()
                ->toArray();
            
            // Combine both - if player has ELO rating AND has played in category, show them
            $playedPlayerIds = array_unique(array_merge($playedInCategory, $playedInMatches));
            
            // Filter rankings to only include players who have actually played
            $rankingsData = array_filter($rankingsData, function($data) use ($playedPlayerIds) {
                // If player has matches_played > 0, they've definitely played
                // OR if they're in the played list
                return $data['matches_played'] > 0 || in_array($data['player_id'], $playedPlayerIds);
            });
            $rankingsData = array_values($rankingsData);
        }
        
        // Get win counts for players with matches
        // For doubles, both players on the winning team should get a win
        $playerIds = collect($rankingsData)->pluck('player_id')->toArray();
        
        // Get all match results for these players
        $matchResults = MatchResult::with('match')
            ->whereHas('match', function($query) use ($playerIds) {
                $query->where(function($q) use ($playerIds) {
                    $q->whereIn('player1_id', $playerIds)
                      ->orWhereIn('player2_id', $playerIds)
                      ->orWhereIn('player1_partner_id', $playerIds)
                      ->orWhereIn('player2_partner_id', $playerIds);
                });
            })
            ->get();
        
        // Count wins for each player (accounting for doubles partners)
        $winCounts = [];
        foreach ($matchResults as $result) {
            $match = $result->match;
            if (!$match || !$match->winner_id) {
                continue;
            }
            
            // Check if this is a doubles match
            $isDoubles = $match->player1_partner_id || $match->player2_partner_id;
            
            if ($isDoubles && $match->winner_partner_id) {
                // Doubles: both players on winning team get a win
                if (in_array($match->winner_id, $playerIds)) {
                    $winCounts[$match->winner_id] = ($winCounts[$match->winner_id] ?? 0) + 1;
                }
                if (in_array($match->winner_partner_id, $playerIds)) {
                    $winCounts[$match->winner_partner_id] = ($winCounts[$match->winner_partner_id] ?? 0) + 1;
                }
            } else {
                // Singles: only winner gets a win
                if (in_array($match->winner_id, $playerIds)) {
                    $winCounts[$match->winner_id] = ($winCounts[$match->winner_id] ?? 0) + 1;
                }
            }
        }
        
        // Count losses for each player (accounting for doubles partners)
        $lossCounts = [];
        foreach ($matchResults as $result) {
            $match = $result->match;
            if (!$match || !$match->winner_id) {
                continue;
            }
            
            // Check if this is a doubles match
            $isDoubles = $match->player1_partner_id || $match->player2_partner_id;
            
            // Determine losing team
            $loserId = null;
            $loserPartnerId = null;
            if ($match->winner_id === $match->player1_id) {
                $loserId = $match->player2_id;
                $loserPartnerId = $match->player2_partner_id;
            } else {
                $loserId = $match->player1_id;
                $loserPartnerId = $match->player1_partner_id;
            }
            
            if ($isDoubles && $loserPartnerId) {
                // Doubles: both players on losing team get a loss
                if (in_array($loserId, $playerIds)) {
                    $lossCounts[$loserId] = ($lossCounts[$loserId] ?? 0) + 1;
                }
                if (in_array($loserPartnerId, $playerIds)) {
                    $lossCounts[$loserPartnerId] = ($lossCounts[$loserPartnerId] ?? 0) + 1;
                }
            } else {
                // Singles: only loser gets a loss
                if (in_array($loserId, $playerIds)) {
                    $lossCounts[$loserId] = ($lossCounts[$loserId] ?? 0) + 1;
                }
            }
        }
        
        // Build final rankings data (without ranks yet)
        // Note: $rankingsData is already sorted by rating descending from RankingService
        $rankings = collect($rankingsData)->map(function ($data) use ($winCounts, $lossCounts) {
            $wins = $winCounts[$data['player_id']] ?? 0;
            $losses = $lossCounts[$data['player_id']] ?? 0;
            
            return [
                'rank' => null, // Will be assigned after ensuring proper sort
                'player' => $data['player'],
                'club' => $data['club'],
                'matches_played' => $data['matches_played'],
                'wins' => $wins,
                'losses' => $losses,
                'win_rate' => $data['matches_played'] > 0 
                    ? round(($wins / $data['matches_played']) * 100, 1) 
                    : 0,
                'current_rating' => $data['current_rating'],
                'peak_rating' => $data['peak_rating'],
                'is_provisional' => $data['is_provisional'] ?? false,
                'has_official_ranking' => $data['has_official_ranking'] ?? ($data['matches_played'] > 0),
            ];
        })->values();
        
        // Ensure proper sorting: official rankings first (by rating descending), then provisional
        $rankings = $rankings->sort(function($a, $b) {
            // If both have official rankings, sort by rating descending (highest first)
            if (($a['has_official_ranking'] ?? false) && ($b['has_official_ranking'] ?? false)) {
                return $b['current_rating'] <=> $a['current_rating'];
            }
            // If only a has official ranking, a comes first
            if ($a['has_official_ranking'] ?? false) {
                return -1;
            }
            // If only b has official ranking, b comes first
            if ($b['has_official_ranking'] ?? false) {
                return 1;
            }
            // Both are provisional (N/A), sort by rating descending
            return $b['current_rating'] <=> $a['current_rating'];
        })->values();
        
        // Now assign ranks based on sorted order (highest rating = rank 1)
        $officialRankCounter = 0;
        $rankings = $rankings->map(function($ranking) use (&$officialRankCounter) {
            if ($ranking['has_official_ranking'] ?? false) {
                $officialRankCounter++;
                $ranking['rank'] = $officialRankCounter;
            } else {
                $ranking['rank'] = null;
            }
            return $ranking;
        });
        
        return view('ranking.index', compact('rankings', 'categories', 'category', 'divisions', 'division'));
    }
}
