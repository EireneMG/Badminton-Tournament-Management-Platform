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
}