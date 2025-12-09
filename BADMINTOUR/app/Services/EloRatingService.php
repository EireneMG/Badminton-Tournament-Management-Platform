<?php

namespace App\Services;

use App\Models\EloRating;
use App\Models\RankingHistory;
use App\Models\User;
use Carbon\Carbon;

class EloRatingService
{
    const K_FACTOR = 32;

    public function calculateMatchRatings(User $player1, User $player2, bool $player1Won, string $categoryType): void
    {
        $player1OldRating = $this->getCurrentRating($player1, $categoryType);
        $player2OldRating = $this->getCurrentRating($player2, $categoryType);

        $player1ExpectedScore = $this->calculateExpectedScore($player1OldRating, $player2OldRating);
        $player2ExpectedScore = 1 - $player1ExpectedScore;
        
        $player1ActualScore = $player1Won ? 1 : 0;
        $player2ActualScore = $player1Won ? 0 : 1;
        
        $player1NewRating = round($player1OldRating + self::K_FACTOR * ($player1ActualScore - $player1ExpectedScore));
        $player2NewRating = round($player2OldRating + self::K_FACTOR * ($player2ActualScore - $player2ExpectedScore));

        $this->updateRating($player1, $categoryType, $player1NewRating);
        $this->updateRating($player2, $categoryType, $player2NewRating);
        
        $this->saveRankingHistory($player1, $categoryType, $player1OldRating, $player1NewRating);
        $this->saveRankingHistory($player2, $categoryType, $player2OldRating, $player2NewRating);
    }

    public function calculateDoublesMatchRatings(User $team1Player1, User $team1Player2, User $team2Player1, User $team2Player2, bool $team1Won, string $categoryType): void
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
        
        $team1RatingChange = self::K_FACTOR * ($team1ActualScore - $team1ExpectedScore);
        $team2RatingChange = self::K_FACTOR * ($team2ActualScore - $team2ExpectedScore);

        $team1Player1NewRating = round($team1Player1OldRating + $team1RatingChange);
        $team1Player2NewRating = round($team1Player2OldRating + $team1RatingChange);
        $team2Player1NewRating = round($team2Player1OldRating + $team2RatingChange);
        $team2Player2NewRating = round($team2Player2OldRating + $team2RatingChange);

        $this->updateRating($team1Player1, $categoryType, $team1Player1NewRating);
        $this->updateRating($team1Player2, $categoryType, $team1Player2NewRating);
        $this->updateRating($team2Player1, $categoryType, $team2Player1NewRating);
        $this->updateRating($team2Player2, $categoryType, $team2Player2NewRating);
        
        $this->saveRankingHistory($team1Player1, $categoryType, $team1Player1OldRating, $team1Player1NewRating);
        $this->saveRankingHistory($team1Player2, $categoryType, $team1Player2OldRating, $team1Player2NewRating);
        $this->saveRankingHistory($team2Player1, $categoryType, $team2Player1OldRating, $team2Player1NewRating);
        $this->saveRankingHistory($team2Player2, $categoryType, $team2Player2OldRating, $team2Player2NewRating);
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

        return 1200; // Default starting rating
    }

    protected function updateRating(User $player, string $categoryType, float $newRating): void
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

    protected function saveRankingHistory(User $player, string $categoryType, float $oldRating, float $newRating): void
    {
        RankingHistory::create([
            'player_id' => $player->id,
            'category' => $categoryType,
            'rating' => $newRating,
            'previous_rating' => $oldRating,
            'change' => $newRating - $oldRating,
            'recorded_at' => Carbon::now(),
        ]);
    }
}
