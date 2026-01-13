<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentCategory;
use App\Models\TournamentMatch;
use App\Models\TournamentRegistration;
use App\Services\EloRatingService;
use App\Enums\CategoryType;
use Carbon\Carbon;

class MatchGenerationService
{
    protected $scheduleService;
    protected $eloService;

    public function __construct(CategoryScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
        $this->eloService = app(EloRatingService::class);
        $this->eloGap = (int) config('elo.gap', env('ELO_FAIR_GAP', 350));
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
            
            // Validate that players match the category type (normalize to avoid "women" containing "men")
            $categoryName = strtolower($category->name ?? '');
            $categoryTypeCode = strtoupper($category->type ?? '');
            $isMixedCategory = str_contains($categoryName, 'mixed') || $categoryTypeCode === CategoryType::MIXED_DOUBLES->value;
            $isWomenCategory = str_contains($categoryName, 'women') || in_array($categoryTypeCode, [CategoryType::WOMENS_SINGLES->value, CategoryType::WOMENS_DOUBLES->value], true);
            $isMenCategory = !$isWomenCategory && (str_contains($categoryName, "men'") || str_contains($categoryName, 'mens') || str_contains($categoryName, 'men ') || in_array($categoryTypeCode, [CategoryType::MENS_SINGLES->value, CategoryType::MENS_DOUBLES->value], true));
            $isDoublesCategory = str_contains($categoryName, 'double') || in_array($categoryTypeCode, [CategoryType::MENS_DOUBLES->value, CategoryType::WOMENS_DOUBLES->value, CategoryType::MIXED_DOUBLES->value], true);
            
            // Filter registrations to ensure players match category gender requirements
            $approvedRegistrations = $approvedRegistrations->filter(function($registration) use ($isMenCategory, $isWomenCategory, $isMixedCategory, $isDoublesCategory) {
                $player = $registration->player;
                $partner = $registration->partner;
                if (!$player) {
                    return false;
                }
                
                $gender = strtolower($player->gender ?? '');
                $partnerGender = $partner ? strtolower($partner->gender ?? '') : null;
                
                if ($isDoublesCategory) {
                    // For doubles categories, enforce both players meet the gender rule
                    if ($isMixedCategory) {
                        // Mixed requires one male + one female
                        if (!$partner) return false;
                        return ($gender === 'male' && $partnerGender === 'female') || ($gender === 'female' && $partnerGender === 'male');
                    }
                    if ($isWomenCategory) {
                        return $gender === 'female' && (!$partner || $partnerGender === 'female');
                    }
                    if ($isMenCategory) {
                        return $gender === 'male' && (!$partner || $partnerGender === 'male');
                    }
                }
                
                if ($isMixedCategory) {
                    return true; // any gender for mixed; pairing handled elsewhere
                }
                if ($isWomenCategory) {
                    return $gender === 'female';
                }
                if ($isMenCategory) {
                    return $gender === 'male';
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
        
        if ($tournament->bracket_type === 'round_robin') {
            $this->normalizeRoundRobinRounds($tournament);
        }
    }
    
    private function normalizeRoundRobinRounds(Tournament $tournament): void
    {
        if ($tournament->bracket_type !== 'round_robin') {
            return;
        }
        
        $matchesByCategory = \App\Models\TournamentMatch::where('tournament_id', $tournament->id)
            ->get()
            ->groupBy('tournament_category_id');
        
        foreach ($matchesByCategory as $categoryId => $matches) {
            $matchesByRound = $matches->groupBy('round');
            $roundData = [];
            
            foreach ($matchesByRound->keys() as $roundName) {
                $r = strtolower(trim((string)$roundName));
                if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                    $roundNum = (int)$m[1];
                    $roundData[] = [
                        'name' => $roundName,
                        'number' => $roundNum
                    ];
                }
            }
            
            if (empty($roundData)) {
                continue;
            }
            
            usort($roundData, function($a, $b) {
                return $a['number'] <=> $b['number'];
            });
            
            $roundMapping = [];
            $newRoundNum = 1;
            
            foreach ($roundData as $roundInfo) {
                $oldRoundName = $roundInfo['name'];
                $newRoundName = "Round {$newRoundNum}";
                $roundMapping[$oldRoundName] = $newRoundName;
                $newRoundNum++;
            }
            
            foreach ($roundMapping as $oldRound => $newRound) {
                \App\Models\TournamentMatch::where('tournament_id', $tournament->id)
                    ->where('tournament_category_id', $categoryId)
                    ->where('round', $oldRound)
                    ->update(['round' => $newRound]);
            }
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

    /**
     * Normalize round name for comparison (handles variations)
     */
    protected function normalizeRoundName(string $roundName): string
    {
        $normalized = strtolower(trim($roundName));
        
        // Map common variations to standard names
        if (str_contains($normalized, 'final') && !str_contains($normalized, 'semi') && !str_contains($normalized, 'quarter')) {
            return 'finals';
        }
        if (str_contains($normalized, 'semi')) {
            return 'semifinals';
        }
        if (str_contains($normalized, 'quarter')) {
            return 'quarterfinals';
        }
        if (preg_match('/round\s+of\s+(\d+)/i', $normalized, $matches)) {
            return 'round of ' . $matches[1];
        }
        if (preg_match('/round\s*(\d+)/i', $normalized, $matches)) {
            return 'round ' . $matches[1];
        }
        
        return $normalized;
    }

    /**
     * Adjust seeds locally to reduce very large ELO gaps in round 1.
     * Keeps overall seeding mostly intact; performs small neighbor swaps when gap exceeds threshold.
     */
    protected function adjustSeedsForFairness(array $seeded, int $eloGap): array
    {
        $count = count($seeded);
        if ($count <= 2) {
            return $seeded;
        }

        // Work on a copy; seeded is already sorted high->low
        for ($i = 0; $i < $count / 2; $i++) {
            $seed1Index = $i;
            $seed2Index = $count - 1 - $i;

            $elo1 = $seeded[$seed1Index]['elo'] ?? 0;
            $elo2 = $seeded[$seed2Index]['elo'] ?? 0;
            $gap = abs($elo1 - $elo2);

            if ($gap <= $eloGap) {
                continue;
            }

            // Try swapping the lower seed with its neighbor to reduce gap
            $swapIndex = $seed2Index - 1;
            if ($swapIndex > $seed1Index) {
                $eloSwap = $seeded[$swapIndex]['elo'] ?? $elo2;
                $newGap = abs($elo1 - $eloSwap);
                if ($newGap < $gap && $newGap <= $eloGap) {
                    // Perform swap
                    $tmp = $seeded[$seed2Index];
                    $seeded[$seed2Index] = $seeded[$swapIndex];
                    $seeded[$swapIndex] = $tmp;
                }
            }
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
        // Use TournamentRoundHelper for consistent round naming
        $maxRounds = count($rounds);
        $roundName = \App\Helpers\TournamentRoundHelper::getRoundName('single_elimination', $roundNumber, $maxRounds);
        
        // Optional fairness adjustment for round 1 to avoid huge ELO gaps
        $seededParticipants = $this->adjustSeedsForFairness($seededParticipants, $this->eloGap);

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
            
            $tournamentDay = \App\Helpers\TournamentDayHelper::calculateTournamentDay($roundNumber, 'single_elimination', $maxRounds);
            
            $scheduledTime = null;
            if ($schedule && isset($schedule['time'])) {
                $scheduledTime = Carbon::createFromTimeString($schedule['time']);
            } else {
                $scheduledTime = Carbon::parse($tournament->start_date)->setTime(9, 0, 0);
            }
            
            $baseDate = Carbon::parse($tournament->start_date);
            $scheduledDate = $baseDate->copy()->addDays($tournamentDay - 1);
            
            $match = TournamentMatch::create([
                'tournament_id' => $tournament->id,
                'tournament_category_id' => $category->id,
                'round' => $roundName,
                'match_number' => $globalMatchNumber++,
                'player1_id' => $registration->player_id,
                'player1_partner_id' => $registration->partner_id,
                'scheduled_date' => $scheduledDate,
                'scheduled_time' => $scheduledTime,
                'tournament_day' => $tournamentDay,
                'court_number' => ($schedule && isset($schedule['court'])) ? $schedule['court'] : (($matchInRound - 1) % $tournament->number_of_courts) + 1,
                'status' => 'completed',
                'winner_id' => $registration->player_id,
                'winner_partner_id' => $registration->partner_id,
            ]);
            $round1Matches[] = $match;
        }
        
        // Pair remaining players for Round 1 matches
        // After byes, pair: Seed (byes+1) vs Seed N, Seed (byes+2) vs Seed (N-1), etc.
        // Example with 10 participants (6 byes): Seed 7 vs Seed 10, Seed 8 vs Seed 9
        $matchInRound = $byes;
        $playingParticipants = $totalParticipants - $byes; // Number of participants who actually play in Round 1
        $actualMatches = $playingParticipants / 2; // Number of actual matches (not byes)
        
        for ($i = 0; $i < $actualMatches; $i++) {
            // Pair from opposite ends: first playing seed vs last playing seed, etc.
            $seed1Index = $byes + $i; // First playing seed index (after byes)
            $seed2Index = $totalParticipants - 1 - $i; // Last playing seed index (counting from end)
            
            $seed1 = $seed1Index + 1; // Actual seed number (1-based)
            $seed2 = $seed2Index + 1; // Actual seed number (1-based)
            
            $participant1 = $seededParticipants[$seed1Index];
            $participant2 = $seededParticipants[$seed2Index];
            
            $registration1 = $participant1['registration'];
            $registration2 = $participant2['registration'];
            
            $matchInRound++;
            
            $schedule = null;
            foreach ($schedules as $sched) {
                if (isset($sched['round_number']) && $sched['round_number'] == $roundNumber && 
                    isset($sched['match_in_round']) && $sched['match_in_round'] == $matchInRound) {
                    $schedule = $sched;
                    break;
                }
            }
            
            $tournamentDay = \App\Helpers\TournamentDayHelper::calculateTournamentDay($roundNumber, 'single_elimination', $maxRounds);
            
            $scheduledTime = null;
            if ($schedule && isset($schedule['time'])) {
                $scheduledTime = Carbon::createFromTimeString($schedule['time']);
            } else {
                $scheduledTime = Carbon::parse($tournament->start_date)->setTime(9, 0, 0);
            }
            
            $baseDate = Carbon::parse($tournament->start_date);
            $scheduledDate = $baseDate->copy()->addDays($tournamentDay - 1);
            
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
                'tournament_day' => $tournamentDay,
                'court_number' => ($schedule && isset($schedule['court'])) ? $schedule['court'] : (($matchInRound - 1) % $tournament->number_of_courts) + 1,
                'status' => 'scheduled',
            ]);
            $round1Matches[] = $match;
        }
        
        // ============================================
        // GENERATE ALL SUBSEQUENT ROUNDS (QF, SF, Finals)
        // ============================================
        $currentRoundMatches = $round1Matches;
        
        for ($roundIndex = 1; $roundIndex < count($rounds); $roundIndex++) {
            $roundNumber = $roundIndex + 1;
            // Use TournamentRoundHelper for consistent round naming
            $roundName = \App\Helpers\TournamentRoundHelper::getRoundName('single_elimination', $roundNumber, $maxRounds);
            $matchesInThisRound = ceil(count($currentRoundMatches) / 2);
            $nextRoundMatches = [];
            
            for ($matchIndex = 0; $matchIndex < $matchesInThisRound; $matchIndex++) {
                $matchInRound = $matchIndex + 1;
                
                $schedule = null;
                foreach ($schedules as $sched) {
                    if (isset($sched['round_number']) && $sched['round_number'] == $roundNumber && 
                        isset($sched['match_in_round']) && $sched['match_in_round'] == $matchInRound) {
                        $schedule = $sched;
                        break;
                    }
                }
                
                $tournamentDay = \App\Helpers\TournamentDayHelper::calculateTournamentDay($roundNumber, 'single_elimination', $maxRounds);
                
                $scheduledTime = null;
                if ($schedule && isset($schedule['time'])) {
                    $scheduledTime = Carbon::createFromTimeString($schedule['time']);
                } else {
                    $scheduledTime = Carbon::parse($tournament->start_date)->setTime(9, 0, 0);
                }
                
                $baseDate = Carbon::parse($tournament->start_date);
                $scheduledDate = $baseDate->copy()->addDays($tournamentDay - 1);
                
                $match = TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'tournament_category_id' => $category->id,
                    'round' => $roundName,
                    'match_number' => $globalMatchNumber++,
                    'player1_id' => null,
                    'player2_id' => null,
                    'player1_partner_id' => null,
                    'player2_partner_id' => null,
                    'scheduled_date' => $scheduledDate,
                    'scheduled_time' => $scheduledTime,
                    'tournament_day' => $tournamentDay,
                    'court_number' => ($schedule && isset($schedule['court'])) ? $schedule['court'] : (($matchIndex % $tournament->number_of_courts) + 1),
                    'status' => 'scheduled',
                ]);
                
                $nextRoundMatches[] = $match;
            }
            
            $currentRoundMatches = $nextRoundMatches;
        }
    }

    protected function generateRoundRobinBracket($category, $registrations, $tournament, array $schedules = []): void
    {
        $participants = $registrations->values()->all();
        $totalParticipants = count($participants);
        
        if ($totalParticipants < 2) {
            return;
        }
        
        $slots = $category->max_participants ?? $totalParticipants;
        $rounds = $this->scheduleService->calculateRoundsForBracket($slots, 'round_robin');
        
        // Use circular rotation method for proper round robin scheduling
        // For n participants: n-1 rounds, each participant plays once per round (except when odd, one sits out)
        $numRounds = $totalParticipants - 1;
        $isOdd = ($totalParticipants % 2) === 1;
        
        // Create participant indices array (0 to n-1)
        $participantIndices = range(0, $totalParticipants - 1);
        
        // If odd, add a "bye" index (-1) to make it even for rotation
        if ($isOdd) {
            $participantIndices[] = -1; // Bye index
        }
        
        $matchNumber = 1;
        
        for ($round = 1; $round <= $numRounds; $round++) {
            $roundName = \App\Helpers\TournamentRoundHelper::getRoundName('round_robin', $round, $numRounds);
            $matchesInRound = floor(count($participantIndices) / 2);
            $roundMatchIndex = 0;
            
            // Pair participants: first with last, second with second-to-last, etc.
            for ($i = 0; $i < $matchesInRound; $i++) {
                $idx1 = $participantIndices[$i];
                $idx2 = $participantIndices[count($participantIndices) - 1 - $i];
                
                // Skip if either index is bye (-1)
                if ($idx1 === -1 || $idx2 === -1) {
                    continue;
                }
                
                $participant1 = $participants[$idx1];
                $participant2 = $participants[$idx2];
                
                if (!$participant1 || !$participant2) {
                    continue;
                }
                
                $roundMatchIndex++;
                
                $schedule = null;
                foreach ($schedules as $sched) {
                    if (isset($sched['round_number']) && $sched['round_number'] == $round && 
                        isset($sched['match_in_round']) && $sched['match_in_round'] == $roundMatchIndex) {
                        $schedule = $sched;
                        break;
                    }
                }
                
                $tournamentDay = \App\Helpers\TournamentDayHelper::calculateTournamentDay($round, 'round_robin', $numRounds);
                
                $scheduledTime = null;
                if ($schedule && isset($schedule['time'])) {
                    $scheduledTime = Carbon::createFromTimeString($schedule['time']);
                } else {
                    $scheduledTime = Carbon::parse($tournament->start_date)->setTime(9, 0, 0);
                }
                
                $baseDate = Carbon::parse($tournament->start_date);
                $scheduledDate = $baseDate->copy()->addDays($tournamentDay - 1);
                
                TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'tournament_category_id' => $category->id,
                    'round' => $roundName,
                    'match_number' => $matchNumber++,
                    'player1_id' => $participant1['player_id'] ?? null,
                    'player2_id' => $participant2['player_id'] ?? null,
                    'player1_partner_id' => $participant1['partner_id'] ?? null,
                    'player2_partner_id' => $participant2['partner_id'] ?? null,
                    'scheduled_date' => $scheduledDate,
                    'scheduled_time' => $scheduledTime,
                    'tournament_day' => $tournamentDay,
                    'court_number' => ($schedule && isset($schedule['court'])) ? $schedule['court'] : (($roundMatchIndex - 1) % $tournament->number_of_courts) + 1,
                    'status' => 'scheduled',
                ]);
            }
            
            // Rotate participants for next round (circular rotation)
            // Standard round robin: Keep first fixed, rotate others clockwise
            // For odd participants: Bye rotates so different player sits out each round
            if ($round < $numRounds) {
                // Remove last element
                $last = array_pop($participantIndices);
                
                // Insert after first element (position 1) to rotate clockwise
                // This rotates all elements including bye, ensuring different player sits out each round
                array_splice($participantIndices, 1, 0, $last);
            }
        }
    }

    public function advanceWinner(TournamentMatch $match, array $schedules = []): void
    {
        if (!$match->winner_id || $match->status !== 'completed') {
            return;
        }

        // Only advance for single elimination tournaments
        if ($match->tournament->bracket_type !== 'single_elimination') {
            return;
        }

        // Determine next round using normalized round names
        $category = TournamentCategory::find($match->tournament_category_id);
        $slots = $category->max_participants ?? 16;
        $rounds = $this->scheduleService->calculateRoundsForBracket($slots, 'single_elimination');
        $maxRounds = count($rounds);
        
        // Find current round index by matching normalized round names
        $currentRoundIndex = -1;
        $currentRoundNormalized = $this->normalizeRoundName($match->round);
        
        for ($i = 1; $i <= $maxRounds; $i++) {
            $roundName = \App\Helpers\TournamentRoundHelper::getRoundName('single_elimination', $i, $maxRounds);
            $normalized = $this->normalizeRoundName($roundName);
            if ($normalized === $currentRoundNormalized) {
                $currentRoundIndex = $i - 1; // Convert to 0-based index
                break;
            }
        }
        
        if ($currentRoundIndex === -1 || $currentRoundIndex >= $maxRounds - 1) {
            return; // Round not found or already at finals
        }
        
        $nextRoundNumber = $currentRoundIndex + 2; // Next round (1-based)
        $nextRoundName = \App\Helpers\TournamentRoundHelper::getRoundName('single_elimination', $nextRoundNumber, $maxRounds);
        
        // Calculate which match in the next round this winner should go to
        // In single elimination: match 1&2 -> match 1, match 3&4 -> match 2, etc.
        // Use match_number for reliable ordering (ensures correct bracket progression)
        $currentRoundMatches = TournamentMatch::where('tournament_id', $match->tournament_id)
            ->where('tournament_category_id', $match->tournament_category_id)
            ->where('round', $match->round)
            ->orderBy('match_number')
            ->get();
        
        // Find position of current match in the ordered list
        $matchPositionInRound = $currentRoundMatches->search(function($m) use ($match) {
            return $m->id === $match->id;
        });
        
        // Calculate next match number: pairs of matches feed into next round
        // Match 0&1 -> Match 0, Match 2&3 -> Match 1, etc.
        if ($matchPositionInRound === false) {
            return; // Match not found in current round
        }
        
        $nextMatchNumber = floor($matchPositionInRound / 2) + 1;

        $nextMatch = TournamentMatch::where('tournament_id', $match->tournament_id)
            ->where('tournament_category_id', $match->tournament_category_id)
            ->where('round', $nextRoundName)
            ->orderBy('match_number')
            ->skip($nextMatchNumber - 1)
            ->first();

        if (!$nextMatch) {
            // Match should already exist (pre-generated with TBD players)
            // If it doesn't exist, something went wrong, but we'll create it as fallback
            return;
        }
        
        // Fill in the TBD player slot in the next match
        // Determine which slot (player1 or player2) based on match position
        $isFirstMatchInPair = ($matchPositionInRound % 2) === 0;
        
        // Handle late inputs: if next match already has players, update the correct slot anyway
        // This allows late result inputs to still update brackets correctly
        if ($isFirstMatchInPair) {
            // This match's winner goes to player1 slot
            // Update even if slot already filled (for late inputs)
            $nextMatch->update([
                'player1_id' => $match->winner_id,
                'player1_partner_id' => $match->winner_partner_id,
            ]);
        } else {
            // This match's winner goes to player2 slot
            // Update even if slot already filled (for late inputs)
            $nextMatch->update([
                'player2_id' => $match->winner_id,
                'player2_partner_id' => $match->winner_partner_id,
            ]);
        }
        
        $nextMatch->refresh();
        
        $previousRoundMatchesComplete = TournamentMatch::where('tournament_id', $match->tournament_id)
            ->where('tournament_category_id', $match->tournament_category_id)
            ->where('round', $match->round)
            ->whereIn('match_number', [($nextMatchNumber - 1) * 2 + 1, ($nextMatchNumber - 1) * 2 + 2])
            ->where('status', '!=', \App\Enums\MatchStatus::COMPLETED->value)
            ->doesntExist();
        
        if ($previousRoundMatchesComplete) {
            if ($nextMatch->player1_id && !$nextMatch->player2_id) {
                $nextMatch->update([
                    'status' => 'completed',
                    'winner_id' => $nextMatch->player1_id,
                    'winner_partner_id' => $nextMatch->player1_partner_id,
                ]);
                $this->advanceWinner($nextMatch, $schedules);
            } elseif ($nextMatch->player2_id && !$nextMatch->player1_id) {
                $nextMatch->update([
                    'status' => 'completed',
                    'winner_id' => $nextMatch->player2_id,
                    'winner_partner_id' => $nextMatch->player2_partner_id,
                ]);
                $this->advanceWinner($nextMatch, $schedules);
            }
        }
    }
}
