<?php

namespace App\Helpers;

class TournamentDayHelper
{
    public static function calculateTournamentDay(int $roundNumber, string $bracketType, int $maxRounds): int
    {
        if ($bracketType === 'round_robin') {
            return $roundNumber;
        }
        
        $roundsFromEnd = $maxRounds - $roundNumber + 1;
        
        if ($roundsFromEnd === 1) {
            return 4;
        } elseif ($roundsFromEnd === 2) {
            return 3;
        } elseif ($roundsFromEnd === 3) {
            return 2;
        } else {
            return 1;
        }
    }
}

