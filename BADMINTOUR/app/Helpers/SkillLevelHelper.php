<?php

namespace App\Helpers;

class SkillLevelHelper
{
    /**
     * Convert ELO rating to skill level
     * 
     * Skill Level Ranges:
     * - Level A: >= 1700 (midpoint between 1800 and 1600)
     * - Level B: >= 1500 (midpoint between 1600 and 1400)
     * - Level C: >= 1300 (midpoint between 1400 and 1200)
     * - Level D: < 1300
     * 
     * @param int|float $eloRating
     * @return string Skill level (A, B, C, or D)
     */
    public static function convertEloToSkillLevel($eloRating): string
    {
        $elo = (int)round($eloRating);
        
        if ($elo >= 1700) {
            return 'A';
        } elseif ($elo >= 1500) {
            return 'B';
        } elseif ($elo >= 1300) {
            return 'C';
        } else {
            return 'D';
        }
    }

    /**
     * Convert skill level to ELO rating (for provisional assignments)
     * 
     * @param string $skillLevel
     * @return int ELO rating
     */
    public static function convertSkillLevelToElo(string $skillLevel): int
    {
        return match($skillLevel) {
            'A' => 1800, // Advanced
            'B' => 1600, // Intermediate-Advanced
            'C' => 1400, // Intermediate
            'D' => 1200, // Beginner
            default => 1400,
        };
    }
}

