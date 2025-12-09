<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentCategory;
use App\Models\TournamentMatch;
use App\Models\TournamentRegistration;
use App\Services\EloRatingService;
use Carbon\Carbon;

class MatchGenerationService
{
    protected $scheduleService;
    protected $eloService;

    public function __construct(CategoryScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
        $this->eloService = app(EloRatingService::class);
    }
    public function generateMatches(Tournament $tournament, string $bracketType = 'single_elimination'): void
    {
        // Step 1: Collect all category data and prepare match information
        $categoryMatchData = [];
        $categoryRegistrations = [];
        
        foreach ($tournament->categories as $category) {
            // Get approved registrations for THIS category only
            $approvedRegistrations = TournamentRegistration::where('category_id', $category->id)
                ->where('tournament_id', $tournament->id)
                ->where('status', 'approved')
                ->with(['player', 'partner'])
                ->get();
            
            // Validate that players match the category type
            $categoryType = strtolower($category->name ?? '');
            $isMenSingles = str_contains($categoryType, "men's singles") || str_contains($categoryType, 'mens singles') || $category->type === 'MS';
            $isWomenSingles = str_contains($categoryType, "women's singles") || str_contains($categoryType, 'womens singles') || $category->type === 'WS';
            $isDoublesCategory = str_contains($categoryType, 'doubles') || str_contains($categoryType, 'mixed') || in_array($category->type, ['MD', 'WD', 'XD']);
            
            // Filter registrations to ensure players match category gender requirements
            $approvedRegistrations = $approvedRegistrations->filter(function($registration) use ($isMenSingles, $isWomenSingles) {
                $player = $registration->player;
                if (!$player) {
                    return false;
                }
                
                // For singles categories, validate gender
                if ($isMenSingles && $player->gender !== 'male') {
                    return false;
                }
                if ($isWomenSingles && $player->gender !== 'female') {
                    return false;
                }
                
                return true;
            });
            
            if ($isDoublesCategory) {
                // Only include registrations with partners (complete teams)
                $approvedRegistrations = $approvedRegistrations->filter(function($registration) {
                    return $registration->partner_id !== null;
                });
                
                // Remove duplicates (each team appears twice - once as player, once as partner)
                $teamIds = [];
                $completeTeams = $approvedRegistrations->filter(function($registration) use (&$teamIds) {
                    $teamKey = min($registration->player_id, $registration->partner_id) . '_' . 
                               max($registration->player_id, $registration->partner_id);
                    if (in_array($teamKey, $teamIds)) {
                        return false;
                    }
                    $teamIds[] = $teamKey;
                    return true;
                });
                
                $approvedRegistrations = $completeTeams->values();
            }
            
            if ($approvedRegistrations->count() < 2) {
                continue;
            }
            
            // Calculate rounds for this category
            $slots = $category->max_participants ?? $approvedRegistrations->count();
            $rounds = $this->scheduleService->calculateRoundsForBracket($slots, $bracketType);
            
            $categoryMatchData[] = [
                'category_id' => $category->id,
                'category_type' => $category->type,
                'rounds' => $rounds,
                'total_matches' => $this->scheduleService->getTotalMatches($slots, $bracketType),
            ];
            
            $categoryRegistrations[$category->id] = [
                'registrations' => $approvedRegistrations,
                'bracket_type' => $bracketType,
            ];
        }
        
        // Step 2: Generate tournament-wide sequential schedule
        if (empty($categoryMatchData)) {
            return; // No valid categories
        }
        
        $tournamentSchedules = $this->scheduleService->generateTournamentSchedule($tournament, $categoryMatchData);
        
        // Step 3: Generate matches for each category using the tournament-wide schedule
        foreach ($categoryMatchData as $catData) {
            $category = TournamentCategory::find($catData['category_id']);
            $categoryId = $category->id;
            $registrations = $categoryRegistrations[$categoryId]['registrations'];
            $bracketType = $categoryRegistrations[$categoryId]['bracket_type'];
            $schedules = $tournamentSchedules[$categoryId] ?? [];
            
            match($bracketType) {
                'single_elimination' => $this->generateSingleEliminationBracket($category, $registrations, $tournament, $schedules),
                'round_robin' => $this->generateRoundRobinBracket($category, $registrations, $tournament, $schedules),
                default => $this->generateSingleEliminationBracket($category, $registrations, $tournament, $schedules),
            };
        }
    }

    /**
     * Seed participants by ELO rating
     * Returns array of seeded participants with ELO and seed number
     */
    protected function seedParticipants($registrations, $category): array
    {
        $categoryType = $category->type; // MS, WS, MD, WD, XD
        $isDoubles = in_array($categoryType, ['MD', 'WD', 'XD']);
        
        $seeded = [];
        
        foreach ($registrations as $registration) {
            if ($isDoubles) {
                // For doubles/mixed doubles, calculate team ELO
                $player1 = $registration->player;
                
                if (!$player1 || !$registration->partner_id) {
                    continue; // Skip incomplete teams
                }
                
                // Get partner - check if it's loaded via relationship or need to fetch
                $player2 = $registration->partner ?? \App\Models\User::find($registration->partner_id);
                
                if (!$player2) {
                    continue; // Skip if partner not found
                }
                
                $elo1 = $this->eloService->getCurrentRating($player1, $categoryType);
                $elo2 = $this->eloService->getCurrentRating($player2, $categoryType);
                $teamElo = ($elo1 + $elo2) / 2; // Average ELO
                
                $seeded[] = [
                    'registration' => $registration,
                    'elo' => $teamElo,
                    'seed' => null, // Will be assigned after sorting
                ];
            } else {
                // Singles - use player's ELO directly
                $player = $registration->player;
                if (!$player) {
                    continue;
                }
                
                $elo = $this->eloService->getCurrentRating($player, $categoryType);
                
                $seeded[] = [
                    'registration' => $registration,
                    'elo' => $elo,
                    'seed' => null,
                ];
            }
        }
        
        // Sort by ELO (highest first), then by registration order if ELO is equal
        usort($seeded, function($a, $b) {
            if ($a['elo'] == $b['elo']) {
                // If ELO is equal, earlier registration = higher seed
                return $a['registration']->id <=> $b['registration']->id;
            }
            // Higher ELO = higher seed (seed 1)
            return $b['elo'] <=> $a['elo'];
        });
        
        // Assign seed numbers (1-based: Seed 1 = highest ELO)
        foreach ($seeded as $index => $item) {
            $seeded[$index]['seed'] = $index + 1;
        }
        
        return $seeded;
    }

    protected function generateSingleEliminationBracket($category, $registrations, $tournament, array $schedules = []): void
    {
        // Seed participants by ELO (highest ELO = Seed 1)
        $seededParticipants = $this->seedParticipants($registrations, $category);
        $totalParticipants = count($seededParticipants);
        
        $slots = $category->max_participants ?? $totalParticipants;
        $rounds = $this->scheduleService->calculateRoundsForBracket($slots, 'single_elimination');
        
        // Calculate total matches needed
        $nextPowerOfTwo = pow(2, ceil(log($totalParticipants, 2)));
        $totalMatchesInRound1 = $nextPowerOfTwo / 2;
        
        // Track match numbers globally
        $globalMatchNumber = 1;
        
        // ============================================
        // ROUND 1: Generate with seeded players (ELO-based pairing)
        // ============================================
        $round1Matches = [];
        $roundNumber = 1;
        $roundName = $rounds[0]['name'] ?? 'Round 1';
        
        // Calculate bracket size and byes
        $nextPowerOfTwo = pow(2, ceil(log($totalParticipants, 2)));
        $byes = $nextPowerOfTwo - $totalParticipants;
        $totalMatchesInRound1 = $nextPowerOfTwo / 2;
        
        // Top seeds get byes (advance automatically)
        for ($i = 0; $i < $byes; $i++) {
            $seed = $i + 1;
            $participant = $seededParticipants[$i];
            $registration = $participant['registration'];
            $matchInRound = $i + 1;
            
            // Find schedule for this bye match
            $schedule = null;
            foreach ($schedules as $sched) {
                if (isset($sched['round_number']) && $sched['round_number'] == $roundNumber && 
                    isset($sched['match_in_round']) && $sched['match_in_round'] == $matchInRound) {
                    $schedule = $sched;
                    break;
                }
            }
            
            // Parse scheduled date and time
            $scheduledDate = $schedule && isset($schedule['date']) 
                ? Carbon::parse($schedule['date']) 
                : Carbon::parse($tournament->start_date);
            $scheduledTime = null;
            if ($schedule && isset($schedule['time'])) {
                $scheduledTime = Carbon::createFromTimeString($schedule['time'])
                    ->setDate($scheduledDate->year, $scheduledDate->month, $scheduledDate->day);
            } else {
                $scheduledTime = Carbon::parse($tournament->start_date)->setTime(9, 0, 0);
            }
            
            // Create bye match (player advances automatically)
            $match = TournamentMatch::create([
                'tournament_id' => $tournament->id,
                'tournament_category_id' => $category->id,
                'round' => $roundName,
                'match_number' => $globalMatchNumber++,
                'player1_id' => $registration->player_id,
                'player1_partner_id' => $registration->partner_id,
                'scheduled_date' => $scheduledDate,
                'scheduled_time' => $scheduledTime,
                'court_number' => ($schedule && isset($schedule['court'])) ? $schedule['court'] : (($matchInRound - 1) % $tournament->number_of_courts) + 1,
                'status' => 'completed',
                'winner_id' => $registration->player_id,
                'winner_partner_id' => $registration->partner_id,
            ]);
            $round1Matches[] = $match;
        }
        
        // Pair remaining players: Seed (byes+1) vs Seed N, Seed (byes+2) vs Seed (N-1), etc.
        $matchInRound = $byes;
        for ($i = $byes; $i < ($totalParticipants + $byes) / 2; $i++) {
            $seed1Index = $i; // Index in array (0-based)
            $seed2Index = $totalParticipants - 1 - ($i - $byes); // Index in array (0-based)
            
            $seed1 = $seed1Index + 1; // Actual seed number (1-based)
            $seed2 = $seed2Index + 1; // Actual seed number (1-based)
            
            $participant1 = $seededParticipants[$seed1Index];
            $participant2 = $seededParticipants[$seed2Index];
            
            $registration1 = $participant1['registration'];
            $registration2 = $participant2['registration'];
            
            $matchInRound++;
            
            // Find schedule for this match
            $schedule = null;
            foreach ($schedules as $sched) {
                if (isset($sched['round_number']) && $sched['round_number'] == $roundNumber && 
                    isset($sched['match_in_round']) && $sched['match_in_round'] == $matchInRound) {
                    $schedule = $sched;
                    break;
                }
            }
            
            // Parse scheduled date and time
            $scheduledDate = $schedule && isset($schedule['date']) 
                ? Carbon::parse($schedule['date']) 
                : Carbon::parse($tournament->start_date);
            $scheduledTime = null;
            if ($schedule && isset($schedule['time'])) {
                $scheduledTime = Carbon::createFromTimeString($schedule['time'])
                    ->setDate($scheduledDate->year, $scheduledDate->month, $scheduledDate->day);
            } else {
                $scheduledTime = Carbon::parse($tournament->start_date)->setTime(9, 0, 0);
            }
            
            // Create match: Seed $seed1 vs Seed $seed2
            $match = TournamentMatch::create([
                'tournament_id' => $tournament->id,
                'tournament_category_id' => $category->id,
                'round' => $roundName,
                'match_number' => $globalMatchNumber++,
                'player1_id' => $registration1->player_id,
                'player2_id' => $registration2->player_id,
                'player1_partner_id' => $registration1->partner_id,
                'player2_partner_id' => $registration2->partner_id,
                'scheduled_date' => $scheduledDate,
                'scheduled_time' => $scheduledTime,
                'court_number' => ($schedule && isset($schedule['court'])) ? $schedule['court'] : (($matchInRound - 1) % $tournament->number_of_courts) + 1,
                'status' => 'scheduled',
            ]);
            $round1Matches[] = $match;
        }
    }
}