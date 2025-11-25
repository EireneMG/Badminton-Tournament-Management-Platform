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
}