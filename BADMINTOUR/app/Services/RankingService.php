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
     * Properly filters by player gender for singles and appropriate categories
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
        // Get all players with ELO ratings (fresh data, no caching)
        // Query directly to ensure we get the latest data from database
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
            // CRITICAL: Filter players by gender for ALL categories
            // MS = Men's Singles, only include male players
            // WS = Women's Singles, only include female players
            // MD = Men's Doubles, only include male players
            // WD = Women's Doubles, only include female players
            // XD = Mixed Doubles, include both genders (players who have played mixed doubles)
            $playerGender = strtolower($player->gender ?? '');
            if ($category === 'MS' && $playerGender !== 'male') {
                continue; // Skip non-male players for Men's Singles
            }
            if ($category === 'WS' && $playerGender !== 'female') {
                continue; // Skip non-female players for Women's Singles
            }
            if ($category === 'MD' && $playerGender !== 'male') {
                continue; // Skip non-male players for Men's Doubles
            }
            if ($category === 'WD' && $playerGender !== 'female') {
                continue; // Skip non-female players for Women's Doubles
            }
            // XD (Mixed Doubles) - both genders can play, so no gender filter here
            // But we'll verify they've actually played in XD category below
            
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
            
            // Get fresh ELO rating from database to ensure we have the latest current_rating
            $eloRating = EloRating::where('player_id', $player->id)
                ->where('category', $category)
                ->first();
            
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
    
    /**
     * Get all rankings across ALL categories, showing each player's HIGHEST ELO
     * 
     * This is used for the "ALL" category view in rankings.
     * Each player appears once with their highest ELO rating across all categories.
     * 
     * @param string|null $division Division filter ('Junior', 'Senior', 'Open', or null for all)
     * @return array
     */
    public function getAllRankingsHighestElo(?string $division = null): array
    {
        // Get all players in clubs
        $allPlayers = User::where('role', 'player')
            ->whereHas('clubMemberships', function($query) {
                $query->where('status', 'approved');
            })
            ->with(['approvedClubMembership.club', 'eloRatings'])
            ->get();
        
        $rankingsData = [];
        
        foreach ($allPlayers as $player) {
            // Apply division filter if specified
            if ($division && in_array($division, ['Junior', 'Senior'])) {
                $playerDivision = $this->playerDivisionService->getPlayerDivision($player);
                if ($playerDivision === null || $playerDivision !== $division) {
                    continue;
                }
            }
            
            // Determine which categories this player can have based on gender
            $playerGender = strtolower($player->gender ?? 'male');
            $possibleCategories = $playerGender === 'female' 
                ? ['WS', 'WD', 'XD'] 
                : ['MS', 'MD', 'XD'];
            
            // Get all ELO ratings for this player (fresh from database)
            $playerEloRatings = EloRating::where('player_id', $player->id)
                ->whereIn('category', $possibleCategories)
                ->get();
            
            $clubMembership = $player->approvedClubMembership;
            
            // Find the highest ELO rating
            $highestRating = null;
            $highestPeakRating = null;
            $totalMatchesPlayed = 0;
            $highestCategory = null;
            
            foreach ($playerEloRatings as $elo) {
                $totalMatchesPlayed += $elo->matches_played;
                if ($highestRating === null || $elo->current_rating > $highestRating) {
                    $highestRating = $elo->current_rating;
                    $highestPeakRating = $elo->peak_rating;
                    $highestCategory = $elo->category;
                }
            }
            
            // If no ELO ratings, check for provisional ELO
            if ($highestRating === null && $clubMembership && $clubMembership->provisional_elo) {
                $highestRating = $clubMembership->provisional_elo;
                $highestPeakRating = $clubMembership->provisional_elo;
            }
            
            // Skip players without any rating
            if ($highestRating === null) {
                continue;
            }
            
            $isProvisional = $playerEloRatings->isEmpty() && $clubMembership && $clubMembership->is_provisional;
            $hasOfficialRanking = $totalMatchesPlayed > 0;
            
            $rankingsData[] = [
                'player_id' => $player->id,
                'player' => $player,
                'club' => $clubMembership?->club,
                'current_rating' => $highestRating,
                'peak_rating' => $highestPeakRating ?? $highestRating,
                'matches_played' => $totalMatchesPlayed,
                'is_provisional' => $isProvisional,
                'has_official_ranking' => $hasOfficialRanking,
                'highest_category' => $highestCategory, // Which category has the highest ELO
            ];
        }
        
        // Sort by rating descending (highest first)
        usort($rankingsData, function($a, $b) {
            return $b['current_rating'] <=> $a['current_rating'];
        });
        
        return $rankingsData;
    }
}

