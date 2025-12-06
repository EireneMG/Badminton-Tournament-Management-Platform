<?php

namespace App\Helpers;

class TournamentRoundHelper
{
    /**
     * Get the display name for a round based on tournament format and round number
     * 
     * @param string $bracketType Tournament format (single_elimination, round_robin)
     * @param int $roundNumber The round number (1, 2, 3, etc.)
     * @param int $maxRounds Total number of rounds in the tournament
     * @param string $bracket Not used (kept for backward compatibility)
     * @return string Display name for the round
     */
    public static function getRoundName(string $bracketType, int $roundNumber, int $maxRounds, string $bracket = 'winners'): string
    {
        return match($bracketType) {
            'single_elimination' => self::getSingleEliminationRoundName($roundNumber, $maxRounds),
            'round_robin' => self::getRoundRobinRoundName($roundNumber),
            default => "Round {$roundNumber}",
        };
    }

    /**
     * Get round name for Single Elimination format
     * 
     * Single Elimination Rounds (from first to last):
     * - Round 1 (First Round / Opening Round)
     * - Round 2 (Second Round)
     * - Round of 16 (if 16+ participants)
     * - Round of 8 (Quarterfinals)
     * - Round of 4 (Semifinals)
     * - Round of 2 (Finals / Championship)
     */
    protected static function getSingleEliminationRoundName(int $roundNumber, int $maxRounds): string
    {
        $roundsFromEnd = $maxRounds - $roundNumber + 1;
        
        return match($roundsFromEnd) {
            1 => 'Finals',
            2 => 'Semifinals',
            3 => 'Quarterfinals',
            4 => 'Round of 16',
            5 => 'Round of 32',
            6 => 'Round of 64',
            default => match($roundNumber) {
                1 => 'First Round',
                2 => 'Second Round',
                default => "Round {$roundNumber}",
            },
        };
    }

    /**
     * Get round name for Round Robin format
     * 
     * Round Robin Rounds:
     * - Round 1, Round 2, Round 3, etc.
     * - Or: Group Stage Round 1, Group Stage Round 2 (if using groups)
     * - Finals (if there's a playoff after round robin)
     */
    protected static function getRoundRobinRoundName(int $roundNumber): string
    {
        return "Round {$roundNumber}";
    }

    /**
     * Get all possible round names for a tournament format
     * This is useful for display purposes or validation
     */
    public static function getPossibleRounds(string $bracketType, int $participantCount): array
    {
        return match($bracketType) {
            'single_elimination' => self::getSingleEliminationRounds($participantCount),
            'round_robin' => self::getRoundRobinRounds($participantCount),
            default => [],
        };
    }

    protected static function getSingleEliminationRounds(int $participantCount): array
    {
        $rounds = [];
        $numRounds = ceil(log($participantCount, 2));
        
        for ($i = 1; $i <= $numRounds; $i++) {
            $roundsFromEnd = $numRounds - $i + 1;
            $rounds[] = match($roundsFromEnd) {
                1 => 'Finals',
                2 => 'Semifinals',
                3 => 'Quarterfinals',
                4 => 'Round of 16',
                5 => 'Round of 32',
                6 => 'Round of 64',
                default => $i === 1 ? 'First Round' : "Round {$i}",
            };
        }
        
        return $rounds;
    }

    protected static function getDoubleEliminationRounds(int $participantCount): array
    {
        $rounds = [];
        $numRounds = ceil(log($participantCount, 2)) * 2 - 1;
        
        // Winners bracket rounds
        $winnersRounds = ceil(log($participantCount, 2));
        for ($i = 1; $i <= $winnersRounds; $i++) {
            $roundsFromEnd = $winnersRounds - $i + 1;
            $rounds[] = match($roundsFromEnd) {
                1 => 'Winners Bracket Finals',
                2 => 'Winners Semifinals',
                3 => 'Winners Quarterfinals',
                default => "Winners Round {$i}",
            };
        }
        
        // Losers bracket rounds
        $losersRounds = $winnersRounds - 1;
        for ($i = 1; $i <= $losersRounds; $i++) {
            $rounds[] = $i === $losersRounds ? 'Losers Bracket Finals' : "Losers Round {$i}";
        }
        
        // Grand Finals
        $rounds[] = 'Grand Finals';
        
        return $rounds;
    }

    protected static function getRoundRobinRounds(int $participantCount): array
    {
        $rounds = [];
        $numRounds = $participantCount - 1;
        
        for ($i = 1; $i <= $numRounds; $i++) {
            $rounds[] = "Round {$i}";
        }
        
        return $rounds;
    }
}

