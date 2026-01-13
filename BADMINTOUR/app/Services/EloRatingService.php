<?php

namespace App\Services;

use App\Models\EloRating;
use App\Models\RankingHistory;
use App\Models\User;
use App\Services\StatisticsService;
use Carbon\Carbon;

class EloRatingService
{
    protected StatisticsService $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    public function calculateMatchRatings(User $player1, User $player2, bool $player1Won, string $categoryType, ?int $tournamentId = null): void
    {
        $player1OldRating = $this->getCurrentRating($player1, $categoryType);
        $player2OldRating = $this->getCurrentRating($player2, $categoryType);

        $player1ExpectedScore = $this->calculateExpectedScore($player1OldRating, $player2OldRating);
        $player2ExpectedScore = 1 - $player1ExpectedScore;
        
        $player1ActualScore = $player1Won ? 1 : 0;
        $player2ActualScore = $player1Won ? 0 : 1;
        
        $kFactor = config('elo.k_factor', 32);
        $player1NewRating = round($player1OldRating + $kFactor * ($player1ActualScore - $player1ExpectedScore));
        $player2NewRating = round($player2OldRating + $kFactor * ($player2ActualScore - $player2ExpectedScore));

        $this->updateRating($player1, $categoryType, $player1NewRating);
        $this->updateRating($player2, $categoryType, $player2NewRating);
        
        $this->saveRankingHistory($player1, $categoryType, $player1OldRating, $player1NewRating, $tournamentId);
        $this->saveRankingHistory($player2, $categoryType, $player2OldRating, $player2NewRating, $tournamentId);
        
        $this->invalidateStatisticsCache($player1);
        $this->invalidateStatisticsCache($player2);
    }

    public function calculateDoublesMatchRatings(User $team1Player1, User $team1Player2, User $team2Player1, User $team2Player2, bool $team1Won, string $categoryType, ?int $tournamentId = null): void
    {
        $team1Player1OldRating = $this->getCurrentRating($team1Player1, $categoryType);
        $team1Player2OldRating = $this->getCurrentRating($team1Player2, $categoryType);
        $team2Player1OldRating = $this->getCurrentRating($team2Player1, $categoryType);
        $team2Player2OldRating = $this->getCurrentRating($team2Player2, $categoryType);
        
        $team1Rating = ($team1Player1OldRating + $team1Player2OldRating) / 2;
        $team2Rating = ($team2Player1OldRating + $team2Player2OldRating) / 2;

        $team1ExpectedScore = $this->calculateExpectedScore($team1Rating, $team2Rating);
        $team2ExpectedScore = 1 - $team1ExpectedScore;
        
        $team1ActualScore = $team1Won ? 1 : 0;
        $team2ActualScore = $team1Won ? 0 : 1;
        
        $kFactor = config('elo.k_factor', 32);
        $team1RatingChange = $kFactor * ($team1ActualScore - $team1ExpectedScore);
        $team2RatingChange = $kFactor * ($team2ActualScore - $team2ExpectedScore);

        $team1Player1NewRating = round($team1Player1OldRating + $team1RatingChange);
        $team1Player2NewRating = round($team1Player2OldRating + $team1RatingChange);
        $team2Player1NewRating = round($team2Player1OldRating + $team2RatingChange);
        $team2Player2NewRating = round($team2Player2OldRating + $team2RatingChange);

        $this->updateRating($team1Player1, $categoryType, $team1Player1NewRating);
        $this->updateRating($team1Player2, $categoryType, $team1Player2NewRating);
        $this->updateRating($team2Player1, $categoryType, $team2Player1NewRating);
        $this->updateRating($team2Player2, $categoryType, $team2Player2NewRating);
        
        $this->saveRankingHistory($team1Player1, $categoryType, $team1Player1OldRating, $team1Player1NewRating, $tournamentId);
        $this->saveRankingHistory($team1Player2, $categoryType, $team1Player2OldRating, $team1Player2NewRating, $tournamentId);
        $this->saveRankingHistory($team2Player1, $categoryType, $team2Player1OldRating, $team2Player1NewRating, $tournamentId);
        $this->saveRankingHistory($team2Player2, $categoryType, $team2Player2OldRating, $team2Player2NewRating, $tournamentId);
        
        $this->invalidateStatisticsCache($team1Player1);
        $this->invalidateStatisticsCache($team1Player2);
        $this->invalidateStatisticsCache($team2Player1);
        $this->invalidateStatisticsCache($team2Player2);
    }

    protected function calculateExpectedScore(float $playerRating, float $opponentRating): float
    {
        return 1 / (1 + pow(10, ($opponentRating - $playerRating) / 400));
    }

    public function getCurrentRating(User $player, string $categoryType): float
    {
        $eloRating = EloRating::where('player_id', $player->id)
            ->where('category', $categoryType)
            ->first();

        if ($eloRating) {
            // Refresh the model to ensure we have the latest current_rating
            $eloRating->refresh();
            return $eloRating->current_rating;
        }

        // If no ELO rating exists, check for provisional ELO from club membership
        $clubMembership = \App\Models\ClubPlayer::where('player_id', $player->id)
            ->where('status', 'approved')
            ->whereNotNull('provisional_elo')
            ->first();

        if ($clubMembership && $clubMembership->is_provisional) {
            return $clubMembership->provisional_elo;
        }

        return config('elo.initial_rating', 1500);
    }

    public function updateRating(User $player, string $categoryType, float $newRating): void
    {
        $rating = EloRating::firstOrNew([
            'player_id' => $player->id,
            'category' => $categoryType,
        ]);
        
        // Check if this is the first match (player had provisional ELO)
        $clubMembership = \App\Models\ClubPlayer::where('player_id', $player->id)
            ->where('status', 'approved')
            ->where('is_provisional', true)
            ->first();
        
        if ($rating->exists) {
            $rating->current_rating = $newRating;
            $rating->peak_rating = max($rating->peak_rating ?? $newRating, $newRating);
            $rating->matches_played = ($rating->matches_played ?? 0) + 1;
        } else {
            // First official match - convert from provisional
            $rating->current_rating = $newRating;
            $rating->peak_rating = $newRating;
            $rating->matches_played = 1;
            
            // Mark provisional as converted
            if ($clubMembership) {
                $clubMembership->update(['is_provisional' => false]);
            }
        }
        
        $rating->save();
        
        // Refresh the model to ensure it's up to date
        $rating->refresh();
        
        // Update skill level based on primary category (MS/WS based on gender)
        // Only update if this is the player's primary category
        $primaryCategory = $player->gender === 'Female' ? 'WS' : 'MS';
        if ($categoryType === $primaryCategory && $rating->matches_played > 0) {
            $this->updateSkillLevelFromElo($player, $newRating);
        }
    }

    /**
     * Update player's skill level based on their ELO rating
     */
    protected function updateSkillLevelFromElo(User $player, float $eloRating): void
    {
        $clubMembership = \App\Models\ClubPlayer::where('player_id', $player->id)
            ->where('status', 'approved')
            ->first();
        
        if ($clubMembership) {
            $newSkillLevel = \App\Helpers\SkillLevelHelper::convertEloToSkillLevel($eloRating);
            $clubMembership->update([
                'skill_level' => $newSkillLevel,
                'is_provisional' => false, // Mark as official since it's based on actual matches
            ]);
        }
    }

    protected function saveRankingHistory(User $player, string $categoryType, float $oldRating, float $newRating, ?int $tournamentId = null): void
    {
        $change = $newRating - $oldRating;
        RankingHistory::create([
            'player_id' => $player->id,
            'category' => $categoryType,
            'rating' => $newRating,
            'previous_rating' => $oldRating,
            'change' => $change,
            'rank' => null, // Rank will be calculated separately if needed
            'tournament_id' => $tournamentId,
            'recorded_at' => Carbon::now(),
        ]);
    }
    
    /**
     * Apply walkover penalty to a player
     * Used when a player forfeits a match (walkover loss)
     * 
     * @param User $player The player receiving the penalty
     * @param string $categoryType The category (MS, WS, MD, WD, XD)
     * @param int|null $penalty Custom penalty amount (default: WALKOVER_PENALTY constant)
     * @return array ['old_rating' => float, 'new_rating' => float, 'penalty' => int]
     */
    public function applyWalkoverPenalty(User $player, string $categoryType, ?int $penalty = null): array
    {
        $penaltyAmount = $penalty ?? config('elo.walkover_penalty', 25);
        $oldRating = $this->getCurrentRating($player, $categoryType);
        $newRating = max(100, $oldRating - $penaltyAmount); // Minimum rating of 100
        
        // Update the rating (but don't increment matches_played for walkover)
        $rating = EloRating::firstOrNew([
            'player_id' => $player->id,
            'category' => $categoryType,
        ]);
        
        if ($rating->exists) {
            $rating->current_rating = $newRating;
            // Don't update peak_rating for penalty (it can only go down)
            // Don't increment matches_played for walkover
        } else {
            // Create new rating record if doesn't exist
            $rating->current_rating = $newRating;
            $rating->peak_rating = $oldRating; // Peak was before penalty
            $rating->matches_played = 0;
        }
        
        $rating->save();
        $rating->refresh();
        
        // Save to ranking history with walkover note
        RankingHistory::create([
            'player_id' => $player->id,
            'category' => $categoryType,
            'rating' => $newRating,
            'previous_rating' => $oldRating,
            'change' => -$penaltyAmount,
            'rank' => null,
            'recorded_at' => Carbon::now(),
        ]);
        
        // Update skill level if this is the player's primary category
        $primaryCategory = $player->gender === 'Female' ? 'WS' : 'MS';
        if ($categoryType === $primaryCategory) {
            $this->updateSkillLevelFromElo($player, $newRating);
        }
        
        return [
            'old_rating' => $oldRating,
            'new_rating' => $newRating,
            'penalty' => $penaltyAmount,
        ];
    }
    
    /**
     * Get the default walkover penalty amount
     */
    public static function getWalkoverPenalty(): int
    {
        return config('elo.walkover_penalty', 25);
    }

    protected function invalidateStatisticsCache(User $player): void
    {
        try {
            $this->statisticsService->invalidatePlayerCache($player);
            
            $club = $player->approvedClubMembership?->club;
            if ($club) {
                $this->statisticsService->invalidateClubCache($club);
                
                if ($club->manager) {
                    $this->statisticsService->invalidateManagerCache($club->manager);
                }
            }
        } catch (\Exception $e) {
            \Log::warning("Failed to invalidate statistics cache for player {$player->id}: " . $e->getMessage());
        }
    }
}
