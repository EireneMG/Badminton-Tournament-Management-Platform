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

    public function calculateRoundsForBracket(int $slots, string $bracketType): array
    {
        $rounds = [];
        
        if ($bracketType === 'single_elimination') {
            $numRounds = ceil(log($slots, 2));
            $remainingMatches = $slots;
            $totalParticipants = $slots;
            
            for ($i = 0; $i < $numRounds; $i++) {
                $roundNumber = $i + 1;
                $matchesInRound = ceil($remainingMatches / 2);
                $participantsInRound = $remainingMatches;
                
                $roundName = $this->getRoundName($roundNumber, $participantsInRound, $totalParticipants, $numRounds);
                
                $rounds[] = [
                    'name' => $roundName,
                    'matches' => $matchesInRound,
                    'round_number' => $roundNumber, // Add round number for sorting
                ];
                
                $remainingMatches = $matchesInRound;
            }
        } else { // round_robin
            $numRounds = $slots - 1;
            $matchesPerRound = floor($slots / 2);
            
            for ($i = 0; $i < $numRounds; $i++) {
                $rounds[] = [
                    'name' => "Round " . ($i + 1),
                    'matches' => $matchesPerRound,
                ];
            }
        }
        
        return $rounds;
    }

    /**
     * Get total number of matches for a bracket type
     */
    public function getTotalMatches(int $slots, string $bracketType): int
    {
        return match($bracketType) {
            'single_elimination' => $slots - 1,
            'round_robin' => ($slots * ($slots - 1)) / 2,
            default => $slots - 1,
        };
    }

    /**
     * Generate tournament-wide schedule for all categories
     * Distributes rounds evenly across available tournament days
     * Allows overlapping start times - splits courts between categories
     * All rounds (including finals) use category's configured start time
     * 
     * @param Tournament $tournament
     * @param array $categoryMatchData Array of ['category_id' => X, 'rounds' => [...], 'total_matches' => Y, 'category_type' => 'MS']
     * @return array Array of schedules indexed by category_id
     */
    public function generateTournamentSchedule($tournament, array $categoryMatchData): array
    {
        $schedules = [];
        $tournamentStartDate = Carbon::parse($tournament->start_date);
        $tournamentEndDate = Carbon::parse($tournament->end_date);
        $numberOfCourts = $tournament->number_of_courts;
        
        // Collect all categories with their start times
        $categories = [];
        foreach ($categoryMatchData as $catData) {
            $categoryId = $catData['category_id'];
            $category = TournamentCategory::find($categoryId);
            if (!$category) continue;
            
            $startTime = $this->parseTime($category->schedule_start_time ?? '09:00');
            $categories[] = [
                'category_id' => $categoryId,
                'category' => $category,
                'type' => $catData['category_type'] ?? 'MS',
                'rounds' => $catData['rounds'] ?? [],
                'start_time' => $startTime,
                'match_duration' => $category->match_duration_minutes ?? $this->getDefaultMatchDuration($catData['category_type'] ?? 'MS'),
                'break_duration' => $category->break_between_matches_minutes ?? 5,
            ];
        }
        
        // Group all matches by round number across all categories
        $matchesByRound = [];
        foreach ($categories as $catInfo) {
            foreach ($catInfo['rounds'] as $roundIndex => $roundInfo) {
                $roundNumber = $roundIndex + 1;
                if (!isset($matchesByRound[$roundNumber])) {
                    $matchesByRound[$roundNumber] = [];
                }
                
                // Add all matches for this category's round
                for ($i = 0; $i < $roundInfo['matches']; $i++) {
                    $matchesByRound[$roundNumber][] = [
                        'category_id' => $catInfo['category_id'],
                        'category_info' => $catInfo,
                        'round_name' => $roundInfo['name'],
                        'round_index' => $roundIndex,
                        'match_in_round' => $i + 1,
                    ];
                }
            }
        }
        
        // Sort rounds by round number
        ksort($matchesByRound);
        
        // If no matches, return empty schedules
        if (empty($matchesByRound)) {
            return $schedules;
        }
        
        $maxRound = max(array_keys($matchesByRound));
        $totalRounds = count($matchesByRound);
        
        // Calculate total available days (inclusive of start and end date)
        $totalDays = $tournamentStartDate->diffInDays($tournamentEndDate) + 1;
        
        // Distribute rounds evenly across available days
        // Round-to-day mapping: [roundNumber => dayOffset]
        $roundToDayMapping = [];
        $roundNumbers = array_keys($matchesByRound);
        sort($roundNumbers);
        
        foreach ($roundNumbers as $index => $roundNumber) {
            if ($totalRounds <= $totalDays) {
                // More days than rounds: distribute evenly, one round per day when possible
                $dayOffset = min($index, $totalDays - 1);
            } else {
                // More rounds than days: distribute evenly across days
                // Handle edge case: if only 1 round, put it on day 0
                if ($totalRounds === 1) {
                    $dayOffset = 0;
                } else {
                    $dayOffset = (int)floor(($index / ($totalRounds - 1)) * ($totalDays - 1));
                }
            }
            $roundToDayMapping[$roundNumber] = $dayOffset;
        }
        
        // Schedule matches by round with even distribution across days
        foreach ($matchesByRound as $roundNumber => $matches) {
            // Get day offset for this round
            $dayOffset = $roundToDayMapping[$roundNumber] ?? 0;
            $currentDate = $tournamentStartDate->copy()->addDays($dayOffset);
            
            // Ensure we don't exceed tournament end date (safety check)
            if ($currentDate->gt($tournamentEndDate)) {
                $currentDate = $tournamentEndDate->copy();
            }
            
            // All rounds (including finals) use category's configured start time
            // No special time handling for finals - consistent with category start time
            
            // Group matches by category
            $matchesByCategory = [];
            foreach ($matches as $match) {
                $catId = $match['category_id'];
                if (!isset($matchesByCategory[$catId])) {
                    $matchesByCategory[$catId] = [];
                }
                $matchesByCategory[$catId][] = $match;
            }
            
            $numCategories = count($matchesByCategory);
            if ($numCategories === 0) {
                continue; // Skip if no categories
            }
            
            // ============================================================
            // COURT ALLOCATION LOGIC
            // ============================================================
            // Case 1: Categories ≤ Courts → All categories run simultaneously, courts split evenly
            // Case 2: Categories > Courts → Categories grouped into batches, batches run sequentially
            // ============================================================
            
            if ($numCategories <= $numberOfCourts) {
                // CASE 1: Categories ≤ Courts - All run simultaneously
                // Split courts evenly among categories
                $baseCourtsPerCategory = floor($numberOfCourts / $numCategories);
                $remainder = $numberOfCourts % $numCategories;
                
                $courtAllocations = [];
                $currentCourt = 1;
                $categoryIndex = 0;
                
                foreach ($matchesByCategory as $catId => $catMatches) {
                    // First 'remainder' categories get one extra court
                    $courtsForThisCategory = $baseCourtsPerCategory + ($categoryIndex < $remainder ? 1 : 0);
                    $catStartCourt = $currentCourt;
                    $catEndCourt = $currentCourt + $courtsForThisCategory - 1;
                    
                    $courtAllocations[$catId] = [
                        'start' => $catStartCourt,
                        'end' => $catEndCourt,
                        'matches' => $catMatches,
                        'batch' => 0, // All in first batch (simultaneous)
                    ];
                    
                    $currentCourt = $catEndCourt + 1;
                    $categoryIndex++;
                }
            } else {
                // CASE 2: Categories > Courts - Group into batches
                // Each batch has at most numberOfCourts categories
                // Batches run sequentially (one batch finishes before next starts)
                $categoryKeys = array_keys($matchesByCategory);
                $batches = array_chunk($categoryKeys, $numberOfCourts, false);
                
                $courtAllocations = [];
                $batchIndex = 0;
                
                foreach ($batches as $batch) {
                    // In each batch, each category gets exactly 1 court
                    $courtNumber = 1;
                    foreach ($batch as $catId) {
                        $courtAllocations[$catId] = [
                            'start' => $courtNumber,
                            'end' => $courtNumber,
                            'matches' => $matchesByCategory[$catId],
                            'batch' => $batchIndex,
                        ];
                        $courtNumber++;
                    }
                    $batchIndex++;
                }
            }
}

