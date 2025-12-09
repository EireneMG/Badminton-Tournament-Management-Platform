<?php

namespace App\Services;

use App\Models\User;
use App\Models\EloRating;
use App\Models\ClubPlayer;
use App\Services\PlayerDivisionService;
use Illuminate\Support\Facades\DB;

class RankingService
{
    protected $playerDivisionService;

    public function __construct(PlayerDivisionService $playerDivisionService)
    {
        $this->playerDivisionService = $playerDivisionService;
    }

    /**
     * Get a player's ranking position for a specific category
     */
    public function getPlayerRanking(User $player, string $category = 'MS'): ?int
    {
        $allRankings = $this->getAllRankings($category);
        
        foreach ($allRankings as $index => $ranking) {
            if ($ranking['player_id'] === $player->id) {
                return $index + 1; // Rank is 1-based
            }
        }
        
        return null; // Player not found in rankings
    }
    
    /**
     * Get all rankings for a category, including players with provisional ELO
     * 
     * Note: Players with provisional ratings (matches_played = 0) are included
     * but should display "No Rank" or "N/A" instead of a numeric rank.
     * Official rankings require at least 1 match played.
     * 
     * @param string $category Category code (MS, WS, MD, WD, XD)
     * @param string|null $division Division filter ('Junior', 'Senior', 'Open', or null for all)
     * @return array
     */
    public function getAllRankings(string $category = 'MS', ?string $division = null): array
    {
        // Get all players with ELO ratings
        $eloRatings = EloRating::with(['player.approvedClubMembership.club'])
            ->where('category', $category)
            ->get()
            ->keyBy('player_id');
        
        // Get all players in clubs (including those without ELO ratings)
        $allPlayers = User::where('role', 'player')
            ->whereHas('clubMemberships', function($query) {
                $query->where('status', 'approved');
            })
            ->with(['approvedClubMembership.club', 'eloRatings'])
            ->get();
        
        // Build rankings including players without ELO (using provisional ELO)
        $rankingsData = [];
        
        foreach ($allPlayers as $player) {
            // Apply division filter if specified
            if ($division && in_array($division, ['Junior', 'Senior'])) {
                $playerDivision = $this->playerDivisionService->getPlayerDivision($player);
                // If player has no birth date, skip them for Junior/Senior filters
                // (they can only appear in 'Open' or 'All' divisions)
                if ($playerDivision === null) {
                    continue; // Skip players without birth date for specific division filters
                }
                if ($playerDivision !== $division) {
                    continue; // Skip players not in the requested division
                }
            } elseif ($division === 'Open') {
                // Open division includes all players, so no filtering needed
            }
            // If division is null or 'All', include all players
            
            $eloRating = $eloRatings->get($player->id);
            $clubMembership = $player->approvedClubMembership;
            
            // Get rating: from ELO if exists, otherwise from provisional ELO
            $currentRating = null;
            if ($eloRating) {
                $currentRating = $eloRating->current_rating;
            } elseif ($clubMembership && $clubMembership->provisional_elo) {
                $currentRating = $clubMembership->provisional_elo;
            } else {
                // Skip players without any rating
                continue;
            }
            
            $matchesPlayed = $eloRating ? $eloRating->matches_played : 0;
            $peakRating = $eloRating ? $eloRating->peak_rating : $currentRating;
            $isProvisional = $eloRating === null && $clubMembership && $clubMembership->is_provisional;
            
            // A player has an official ranking only if they have played at least 1 match
            // Players with provisional ratings (matches_played = 0) should not have a numeric rank
            $hasOfficialRanking = $matchesPlayed > 0;
            
            $rankingsData[] = [
                'player_id' => $player->id,
                'player' => $player,
                'club' => $clubMembership?->club,
                'current_rating' => $currentRating,
                'peak_rating' => $peakRating,
                'matches_played' => $matchesPlayed,
                'is_provisional' => $isProvisional,
                'has_official_ranking' => $hasOfficialRanking,
            ];
        }
        
        // Sort by rating descending
        usort($rankingsData, function($a, $b) {
            return $b['current_rating'] <=> $a['current_rating'];
        });
        
        return $rankingsData;
    }
    
    /**
     * Get player's current rating (ELO or provisional)
     */
    public function getPlayerRating(User $player, string $category = 'MS'): ?float
    {
        $eloRating = EloRating::where('player_id', $player->id)
            ->where('category', $category)
            ->first();
        
        if ($eloRating) {
            return $eloRating->current_rating;
        }
        
        // Check for provisional ELO
        $clubMembership = ClubPlayer::where('player_id', $player->id)
            ->where('status', 'approved')
            ->whereNotNull('provisional_elo')
            ->first();
        
        if ($clubMembership && $clubMembership->provisional_elo) {
            return $clubMembership->provisional_elo;
        }
        
        return null;
    }
}

