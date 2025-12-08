<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentCategory;
use Carbon\Carbon;

class CategoryScheduleService
{
    /**
     * Calculate match duration based on category type
     */
    public function getDefaultMatchDuration(string $categoryType): int
    {
        $categoryType = strtoupper($categoryType);
        
        // Singles: 45 minutes, Doubles/Mixed: 60 minutes
        if (in_array($categoryType, ['MS', 'WS'])) {
            return 45; // Singles
        } elseif (in_array($categoryType, ['MD', 'WD', 'XD'])) {
            return 60; // Doubles/Mixed
        }
        
        return 45; // Default
    }

    /**
     * Generate schedule for all matches in a category
     * 
     * @param TournamentCategory $category
     * @param int $totalMatches Number of matches in the category
     * @param string $bracketType Tournament bracket type
     * @param int $numberOfCourts Available courts
     * @param array $rounds Array of round information [['name' => 'Round 1', 'matches' => 8], ...]
     * @return array Array of schedule entries [['round' => 'Round 1', 'match_number' => 1, 'date' => '2024-01-01', 'time' => '09:00', 'court' => 1], ...]
     */
    public function generateCategorySchedule(
        TournamentCategory $category,
        int $totalMatches,
        string $bracketType,
        int $numberOfCourts,
        array $rounds
    ): array {
        $schedules = [];
        
        // Get category schedule configuration
        $startDate = $category->schedule_start_date ?? $category->tournament->start_date;
        
        // Handle schedule_start_time - it can be a time string or datetime
        $startTime = '09:00';
        if ($category->schedule_start_time) {
            if (is_string($category->schedule_start_time)) {
                // If it's already in H:i format, use it directly
                if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $category->schedule_start_time)) {
                    $startTime = substr($category->schedule_start_time, 0, 5); // Get HH:mm
                } else {
                    // Try to parse as datetime
                    try {
                        $startTime = Carbon::parse($category->schedule_start_time)->format('H:i');
                    } catch (\Exception $e) {
                        $startTime = '09:00';
                    }
                }
            }
        }
        $matchDuration = $category->match_duration_minutes ?? $this->getDefaultMatchDuration($category->type);
        $breakDuration = $category->break_between_matches_minutes ?? 5;
        
        $currentDate = Carbon::parse($startDate);
        $currentTime = Carbon::createFromTimeString($startTime);
        $courtNumber = 1;
        $matchNumber = 1;
        
        foreach ($rounds as $roundIndex => $roundInfo) {
            $roundName = $roundInfo['name'];
            $matchesInRound = $roundInfo['matches'];
            
            // Reset time for each round (start of day)
            if ($roundIndex > 0) {
                // Move to next day for new round (except for finals which can be same day)
                $currentDate->addDay();
                $currentTime = Carbon::createFromTimeString($startTime);
            }
            
            // All rounds (including finals) use the category's configured start time
            // No special time handling - consistent scheduling throughout
            
            // Distribute matches across courts
            for ($i = 0; $i < $matchesInRound; $i++) {
                $schedules[] = [
                    'round' => $roundName,
                    'round_number' => $roundIndex + 1,
                    'match_number' => $matchNumber++,
                    'date' => $currentDate->format('Y-m-d'),
                    'time' => $currentTime->format('H:i'),
                    'court' => $courtNumber,
                ];
                
                // Move to next court
                $courtNumber = ($courtNumber % $numberOfCourts) + 1;
                
                // If we've used all courts, move to next time slot
                if ($courtNumber === 1 && $i < $matchesInRound - 1) {
                    $currentTime->addMinutes($matchDuration + $breakDuration);
                    
                    // If time exceeds 8 PM, move to next day
                    if ($currentTime->format('H:i') >= '20:00') {
                        $currentDate->addDay();
                        $currentTime = Carbon::createFromTimeString($startTime);
                    }
                }
            }
            
            // Add extra break between rounds
            if ($roundIndex < count($rounds) - 1) {
                $currentTime->addMinutes(30); // 30-minute break between rounds
                
                // If time exceeds 8 PM, move to next day
                if ($currentTime->format('H:i') >= '20:00') {
                    $currentDate->addDay();
                    $currentTime = Carbon::createFromTimeString($startTime);
                }
            }
        }
        
        return $schedules;
    }

    /**
     * Calculate rounds for a bracket type
     */
    /**
     * Get round name based on number of participants and round number
     */
    protected function getRoundName(int $roundNumber, int $participantsInRound, int $totalParticipants, int $totalRounds): string
    {
        // Round 1: Show participant count (e.g., "Round 1 (Round of 12)")
        if ($roundNumber === 1) {
            return "Round 1 (Round of {$totalParticipants})";
        }
        
        // Last round: Finals
        if ($roundNumber === $totalRounds) {
            return "Finals";
        }
        
        // Second to last: Semifinals
        if ($roundNumber === $totalRounds - 1) {
            return "Semifinals";
        }
        
        // Third to last: Quarterfinals
        if ($roundNumber === $totalRounds - 2) {
            return "Quarterfinals";
        }
        
        // Other rounds: Round N (Round of X)
        return "Round {$roundNumber} (Round of {$participantsInRound})";
    }

   
}

