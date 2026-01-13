<?php

namespace App\Services;

use App\Models\MatchResult;
use App\Models\TournamentMatch;

/**
 * Service for handling match score calculations and formatting
 */
class MatchScoreService
{
    /**
     * Validate a single set score according to badminton rules
     * Rules:
     * - First to 21 points wins, but must win by at least 2 points
     * - If score reaches 29-29, first to 30 wins
     * - Maximum score is 30
     * 
     * Returns: ['valid' => bool, 'winner' => 'player1'|'player2'|null, 'error' => string|null]
     */
    public function validateSetScore(int $p1Score, int $p2Score): array
    {
        // Check maximum score
        if ($p1Score > 30 || $p2Score > 30) {
            return ['valid' => false, 'winner' => null, 'error' => 'Maximum score per set is 30 points.'];
        }
        
        // Special case: Allow 21-0 or 0-21 for walkover scenarios (absent player)
        // This is the simplest way to handle walkovers - manager inputs 21-0 for present player
        if (($p1Score === 21 && $p2Score === 0) || ($p1Score === 0 && $p2Score === 21)) {
            $winner = $p1Score === 21 ? 'player1' : 'player2';
            return ['valid' => true, 'winner' => $winner, 'error' => null];
        }
        
        // Check if scores are equal (set cannot end in a tie)
        if ($p1Score === $p2Score) {
            return ['valid' => false, 'winner' => null, 'error' => 'A set cannot end in a tie. One player must win by at least 2 points.'];
        }
        
        $higher = max($p1Score, $p2Score);
        $lower = min($p1Score, $p2Score);
        $diff = $higher - $lower;
        
        // Check 29-29 tiebreaker rule: if both reach 29, first to 30 wins
        if ($p1Score === 29 && $p2Score === 29) {
            return ['valid' => false, 'winner' => null, 'error' => 'If score is 29-29, the set must continue until one player reaches 30.'];
        }
        
        // If one player reaches 30, they must have won from 29-29
        if ($higher === 30) {
            if ($lower !== 29) {
                return ['valid' => false, 'winner' => null, 'error' => 'A score of 30 can only occur when the set was tied at 29-29.'];
            }
            // 30-29 is valid (winner from 29-29 tiebreaker)
            $winner = $p1Score === 30 ? 'player1' : 'player2';
            return ['valid' => true, 'winner' => $winner, 'error' => null];
        }
        
        // If neither reached 30, check if someone reached 21
        if ($higher < 21) {
            return ['valid' => false, 'winner' => null, 'error' => 'A set must be won by reaching at least 21 points.'];
        }
        
        // If someone reached 21 or more, they must win by at least 2 points
        if ($higher >= 21 && $diff < 2) {
            return ['valid' => false, 'winner' => null, 'error' => 'A set must be won by at least 2 points (e.g., 21-19, 22-20, 23-21).'];
        }
        
        // Valid set score
        $winner = $p1Score > $p2Score ? 'player1' : 'player2';
        return ['valid' => true, 'winner' => $winner, 'error' => null];
    }
    
    /**
     * Calculate which player/team won each set
     * Returns array with set winners: ['set1' => 'player1'|'player2', 'set2' => ..., 'set3' => ...]
     */
    public function calculateSetWinners(MatchResult $result): array
    {
        $winners = [];
        
        // Set 1
        if ($result->player1_set1_score !== null && $result->player2_set1_score !== null) {
            $validation = $this->validateSetScore($result->player1_set1_score, $result->player2_set1_score);
            if ($validation['valid']) {
                $winners['set1'] = $validation['winner'];
            }
        }
        
        // Set 2
        if ($result->player1_set2_score !== null && $result->player2_set2_score !== null) {
            $validation = $this->validateSetScore($result->player1_set2_score, $result->player2_set2_score);
            if ($validation['valid']) {
                $winners['set2'] = $validation['winner'];
            }
        }
        
        // Set 3
        if ($result->player1_set3_score !== null && $result->player2_set3_score !== null) {
            $validation = $this->validateSetScore($result->player1_set3_score, $result->player2_set3_score);
            if ($validation['valid']) {
                $winners['set3'] = $validation['winner'];
            }
        }
        
        return $winners;
    }
    
    /**
     * Calculate final match score (e.g., "2-0" or "2-1")
     * Returns array: ['player1_sets' => int, 'player2_sets' => int, 'final_score' => '2-0'|'2-1']
     */
    public function calculateFinalScore(MatchResult $result): array
    {
        $setWinners = $this->calculateSetWinners($result);
        
        $player1Sets = 0;
        $player2Sets = 0;
        
        foreach ($setWinners as $set => $winner) {
            if ($winner === 'player1') {
                $player1Sets++;
            } else {
                $player2Sets++;
            }
        }
        
        return [
            'player1_sets' => $player1Sets,
            'player2_sets' => $player2Sets,
            'final_score' => "{$player1Sets}-{$player2Sets}",
        ];
    }
    
    /**
     * Get formatted set scores for display
     * Returns array: ['set1' => '21-15', 'set2' => '21-17', 'set3' => null|'21-19']
     */
    public function getFormattedSetScores(MatchResult $result): array
    {
        $scores = [];
        
        if ($result->player1_set1_score !== null && $result->player2_set1_score !== null) {
            $scores['set1'] = "{$result->player1_set1_score}-{$result->player2_set1_score}";
        }
        
        if ($result->player1_set2_score !== null && $result->player2_set2_score !== null) {
            $scores['set2'] = "{$result->player1_set2_score}-{$result->player2_set2_score}";
        }
        
        if ($result->player1_set3_score !== null && $result->player2_set3_score !== null) {
            $scores['set3'] = "{$result->player1_set3_score}-{$result->player2_set3_score}";
        }
        
        return $scores;
    }
    
    /**
     * Get complete score display string
     * Returns: "Final Score: 2-0\nSet Scores:\n21-15\n21-17"
     */
    public function getCompleteScoreDisplay(MatchResult $result): string
    {
        $finalScore = $this->calculateFinalScore($result);
        $setScores = $this->getFormattedSetScores($result);
        
        $display = "Final Score: {$finalScore['final_score']}\n";
        $display .= "Set Scores:\n";
        
        foreach ($setScores as $set => $score) {
            $display .= $score . "\n";
        }
        
        return trim($display);
    }
    
    /**
     * Check if Set 3 is required based on Set 1 and Set 2 scores
     * Returns true if sets are tied 1-1, false if one player/team won both sets
     */
    public function isSet3Required(?int $p1Set1, ?int $p2Set1, ?int $p1Set2, ?int $p2Set2): bool
    {
        if ($p1Set1 === null || $p2Set1 === null || $p1Set2 === null || $p2Set2 === null) {
            return false; // Can't determine if Set 3 is needed without both sets
        }
        
        // Validate set scores first
        $set1Validation = $this->validateSetScore($p1Set1, $p2Set1);
        $set2Validation = $this->validateSetScore($p1Set2, $p2Set2);
        
        if (!$set1Validation['valid'] || !$set2Validation['valid']) {
            return false; // Can't determine if Set 3 is needed with invalid scores
        }
        
        $set1Winner = $set1Validation['winner'];
        $set2Winner = $set2Validation['winner'];
        
        // Set 3 is required if sets are tied 1-1
        return $set1Winner !== $set2Winner;
    }
    
    /**
     * Validate set scores and determine if Set 3 is required
     * Returns array with validation info including badminton rule validation
     */
    public function validateSetScores(array $scores): array
    {
        $p1Set1 = $scores['player1_set1_score'] ?? null;
        $p2Set1 = $scores['player2_set1_score'] ?? null;
        $p1Set2 = $scores['player1_set2_score'] ?? null;
        $p2Set2 = $scores['player2_set2_score'] ?? null;
        $p1Set3 = $scores['player1_set3_score'] ?? null;
        $p2Set3 = $scores['player2_set3_score'] ?? null;
        
        $errors = [];
        
        // Validate Set 1
        if ($p1Set1 !== null && $p2Set1 !== null) {
            $set1Validation = $this->validateSetScore($p1Set1, $p2Set1);
            if (!$set1Validation['valid']) {
                $errors['set1'] = $set1Validation['error'];
            }
        }
        
        // Validate Set 2
        if ($p1Set2 !== null && $p2Set2 !== null) {
            $set2Validation = $this->validateSetScore($p1Set2, $p2Set2);
            if (!$set2Validation['valid']) {
                $errors['set2'] = $set2Validation['error'];
            }
        }
        
        // Validate Set 3 (if provided)
        if ($p1Set3 !== null && $p2Set3 !== null) {
            $set3Validation = $this->validateSetScore($p1Set3, $p2Set3);
            if (!$set3Validation['valid']) {
                $errors['set3'] = $set3Validation['error'];
            }
        }
        
        // Check if Set 3 is required
        $set3Required = false;
        if ($p1Set1 !== null && $p2Set1 !== null && $p1Set2 !== null && $p2Set2 !== null) {
            if (empty($errors['set1']) && empty($errors['set2'])) {
                $set3Required = $this->isSet3Required($p1Set1, $p2Set1, $p1Set2, $p2Set2);
            }
        }
        
        return [
            'set3_required' => $set3Required,
            'set3_provided' => $p1Set3 !== null && $p2Set3 !== null,
            'valid' => empty($errors) && (!$set3Required || ($p1Set3 !== null && $p2Set3 !== null)),
            'errors' => $errors,
        ];
    }
    
    /**
     * Process match result and update ELO ratings
     * This method connects MatchScoreService to ELO/Ranking logic
     * Prevents double ELO updates by checking if ELO was already updated
     * 
     * @param TournamentMatch $match The match with result
     * @param MatchResult $result The match result
     * @return array ['success' => bool, 'message' => string, 'elo_updates' => array]
     */
    public function processMatchResultAndUpdateElo(TournamentMatch $match, MatchResult $result): array
    {
        try {
            // Check if ELO was already updated for this match (prevent double updates)
            if ($result->elo_updated ?? false) {
                return [
                    'success' => true,
                    'message' => 'ELO ratings were already updated for this match.',
                    'elo_updates' => []
                ];
            }
            
            // Validate that result exists and has scores
            if (!$result || !$result->player1_set1_score || !$result->player2_set1_score) {
                return [
                    'success' => false,
                    'message' => 'Match result is incomplete. Cannot update ELO ratings.',
                    'elo_updates' => []
                ];
            }
            
            // Get match category
            $category = $match->category;
            if (!$category) {
                return [
                    'success' => false,
                    'message' => 'Match category not found. Cannot update ELO ratings.',
                    'elo_updates' => []
                ];
            }
            
            $categoryType = $category->type; // MS, WS, MD, WD, XD
            $isDoubles = in_array($categoryType, ['MD', 'WD', 'XD']);
            
            // Get players
            $player1 = $match->player1;
            $player2 = $match->player2;
            
            if (!$player1 || !$player2) {
                return [
                    'success' => false,
                    'message' => 'One or more players not found. Cannot update ELO ratings.',
                    'elo_updates' => []
                ];
            }
            
            // Determine winner from result
            $finalScore = $this->calculateFinalScore($result);
            $player1Won = $finalScore['player1_sets'] > $finalScore['player2_sets'];
            
            // Get ELO service
            $eloService = app(\App\Services\EloRatingService::class);
            
            $eloUpdates = [];
            
            if ($isDoubles && $match->player1_partner_id && $match->player2_partner_id) {
                // Doubles/Mixed Doubles
                $player1Partner = $match->player1Partner;
                $player2Partner = $match->player2Partner;
                
                if (!$player1Partner || !$player2Partner) {
                    return [
                        'success' => false,
                        'message' => 'One or more partners not found. Cannot update ELO ratings.',
                        'elo_updates' => []
                    ];
                }
                
                // Get old ratings
                $p1OldElo = $eloService->getCurrentRating($player1, $categoryType);
                $p1pOldElo = $eloService->getCurrentRating($player1Partner, $categoryType);
                $p2OldElo = $eloService->getCurrentRating($player2, $categoryType);
                $p2pOldElo = $eloService->getCurrentRating($player2Partner, $categoryType);
                
                // Calculate and update ELO ratings
                $eloService->calculateDoublesMatchRatings(
                    $player1,
                    $player1Partner,
                    $player2,
                    $player2Partner,
                    $player1Won,
                    $categoryType,
                    $match->tournament_id
                );
                
                // Get new ratings
                $p1NewElo = $eloService->getCurrentRating($player1, $categoryType);
                $p1pNewElo = $eloService->getCurrentRating($player1Partner, $categoryType);
                $p2NewElo = $eloService->getCurrentRating($player2, $categoryType);
                $p2pNewElo = $eloService->getCurrentRating($player2Partner, $categoryType);
                
                $eloUpdates = [
                    'player1' => [
                        'id' => $player1->id,
                        'name' => trim(($player1->first_name ?? '') . ' ' . ($player1->last_name ?? '')),
                        'old_elo' => $p1OldElo,
                        'new_elo' => $p1NewElo,
                        'change' => $p1NewElo - $p1OldElo,
                    ],
                    'player1_partner' => [
                        'id' => $player1Partner->id,
                        'name' => trim(($player1Partner->first_name ?? '') . ' ' . ($player1Partner->last_name ?? '')),
                        'old_elo' => $p1pOldElo,
                        'new_elo' => $p1pNewElo,
                        'change' => $p1pNewElo - $p1pOldElo,
                    ],
                    'player2' => [
                        'id' => $player2->id,
                        'name' => trim(($player2->first_name ?? '') . ' ' . ($player2->last_name ?? '')),
                        'old_elo' => $p2OldElo,
                        'new_elo' => $p2NewElo,
                        'change' => $p2NewElo - $p2OldElo,
                    ],
                    'player2_partner' => [
                        'id' => $player2Partner->id,
                        'name' => trim(($player2Partner->first_name ?? '') . ' ' . ($player2Partner->last_name ?? '')),
                        'old_elo' => $p2pOldElo,
                        'new_elo' => $p2pNewElo,
                        'change' => $p2pNewElo - $p2pOldElo,
                    ],
                ];
            } else {
                // Singles
                // Get old ratings
                $p1OldElo = $eloService->getCurrentRating($player1, $categoryType);
                $p2OldElo = $eloService->getCurrentRating($player2, $categoryType);
                
                // Calculate and update ELO ratings
                $eloService->calculateMatchRatings(
                    $player1,
                    $player2,
                    $player1Won,
                    $categoryType,
                    $match->tournament_id
                );
                
                // Get new ratings
                $p1NewElo = $eloService->getCurrentRating($player1, $categoryType);
                $p2NewElo = $eloService->getCurrentRating($player2, $categoryType);
                
                $eloUpdates = [
                    'player1' => [
                        'id' => $player1->id,
                        'name' => trim(($player1->first_name ?? '') . ' ' . ($player1->last_name ?? '')),
                        'old_elo' => $p1OldElo,
                        'new_elo' => $p1NewElo,
                        'change' => $p1NewElo - $p1OldElo,
                    ],
                    'player2' => [
                        'id' => $player2->id,
                        'name' => trim(($player2->first_name ?? '') . ' ' . ($player2->last_name ?? '')),
                        'old_elo' => $p2OldElo,
                        'new_elo' => $p2NewElo,
                        'change' => $p2NewElo - $p2OldElo,
                    ],
                ];
            }
            
            // Send notifications to all players about match result
            $playersToNotify = [];
            if ($player1) {
                $playersToNotify[] = $player1->id;
            }
            if ($player2) {
                $playersToNotify[] = $player2->id;
            }
            
            // Add partners for doubles/mixed doubles
            if ($isDoubles && $match->player1_partner_id) {
                $playersToNotify[] = $match->player1_partner_id;
            }
            if ($isDoubles && $match->player2_partner_id) {
                $playersToNotify[] = $match->player2_partner_id;
            }
            
            // Mark ELO as updated to prevent double updates
            $result->elo_updated = true;
            $result->save();
            
            // Remove duplicates and send notifications
            $playersToNotify = array_unique(array_filter($playersToNotify));
            foreach ($playersToNotify as $playerId) {
                try {
                    \App\Models\Notification::create([
                        'user_id' => $playerId,
                        'type' => 'match_result_posted',
                        'title' => 'Match Result Posted',
                        'message' => "The result for your match in {$match->tournament->name} has been posted.",
                        'data' => ['match_id' => $match->id],
                        'action_url' => route('player.matches.show', $match->id),
                    ]);
                } catch (\Exception $e) {
                    // Log error but don't fail the entire operation
                    \Log::warning("Failed to send notification to player {$playerId}: " . $e->getMessage());
                }
            }
            
            return [
                'success' => true,
                'message' => 'ELO ratings updated successfully.',
                'elo_updates' => $eloUpdates,
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error updating ELO ratings from match result', [
                'match_id' => $match->id,
                'result_id' => $result->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Error updating ELO ratings: ' . $e->getMessage(),
                'elo_updates' => []
            ];
        }
    }
}

