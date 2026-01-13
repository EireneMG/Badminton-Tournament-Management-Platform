<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentCategory;
use App\Models\TournamentMatch;
use App\Models\TournamentRegistration;
use App\Models\MatchResult;

class RoundRobinStandingsService
{
    /**
     * Calculate standings for a round robin tournament category
     * 
     * @param TournamentCategory $category
     * @return array Array of standings with rank, participant, wins, losses, sets won, sets lost, points for, points against
     */
    public function calculateStandings(TournamentCategory $category): array
    {
        $tournament = $category->tournament;
        if ($tournament->bracket_type !== 'round_robin') {
            return [];
        }
        
        // Get all approved registrations for this category
        $registrations = TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('category_id', $category->id)
            ->where('status', 'approved')
            ->with(['player', 'partner'])
            ->get();
        
        // Initialize standings for each participant
        $standings = [];
        foreach ($registrations as $registration) {
            $participantId = $this->getParticipantKey($registration);
            $standings[$participantId] = [
                'registration' => $registration,
                'participant_id' => $participantId,
                'player_id' => $registration->player_id,
                'partner_id' => $registration->partner_id,
                'player_name' => $registration->player ? trim(($registration->player->first_name ?? '') . ' ' . ($registration->player->last_name ?? '')) : 'Unknown',
                'partner_name' => $registration->partner ? trim(($registration->partner->first_name ?? '') . ' ' . ($registration->partner->last_name ?? '')) : null,
                'team_name' => $this->getTeamName($registration),
                'matches_played' => 0,
                'wins' => 0,
                'losses' => 0,
                'sets_won' => 0,
                'sets_lost' => 0,
                'points_for' => 0,
                'points_against' => 0,
                'point_difference' => 0,
                'head_to_head_wins' => 0,
                'rank' => 0,
            ];
        }
        
        // Get all completed matches for this category
        $matches = TournamentMatch::where('tournament_id', $tournament->id)
            ->where('tournament_category_id', $category->id)
            ->where('status', 'completed')
            ->with(['result', 'player1', 'player2', 'player1Partner', 'player2Partner'])
            ->get();
        
        // Process each match result
        foreach ($matches as $match) {
            $result = $match->result;
            if (!$result) {
                continue;
            }
            
            $player1Key = $this->getMatchParticipantKey($match, 'player1');
            $player2Key = $this->getMatchParticipantKey($match, 'player2');
            
            if (!isset($standings[$player1Key]) || !isset($standings[$player2Key])) {
                continue;
            }
            
            // Calculate sets won/lost
            $player1Sets = 0;
            $player2Sets = 0;
            $player1Points = 0;
            $player2Points = 0;
            
            // Set 1
            if ($result->player1_set1_score !== null && $result->player2_set1_score !== null) {
                if ($result->player1_set1_score > $result->player2_set1_score) {
                    $player1Sets++;
                } else {
                    $player2Sets++;
                }
                $player1Points += $result->player1_set1_score;
                $player2Points += $result->player2_set1_score;
            }
            
            // Set 2
            if ($result->player1_set2_score !== null && $result->player2_set2_score !== null) {
                if ($result->player1_set2_score > $result->player2_set2_score) {
                    $player1Sets++;
                } else {
                    $player2Sets++;
                }
                $player1Points += $result->player1_set2_score;
                $player2Points += $result->player2_set2_score;
            }
            
            // Set 3
            if ($result->player1_set3_score !== null && $result->player2_set3_score !== null) {
                if ($result->player1_set3_score > $result->player2_set3_score) {
                    $player1Sets++;
                } else {
                    $player2Sets++;
                }
                $player1Points += $result->player1_set3_score;
                $player2Points += $result->player2_set3_score;
            }
            
            // Update standings
            $standings[$player1Key]['matches_played']++;
            $standings[$player2Key]['matches_played']++;
            
            $standings[$player1Key]['sets_won'] += $player1Sets;
            $standings[$player1Key]['sets_lost'] += $player2Sets;
            $standings[$player2Key]['sets_won'] += $player2Sets;
            $standings[$player2Key]['sets_lost'] += $player1Sets;
            
            $standings[$player1Key]['points_for'] += $player1Points;
            $standings[$player1Key]['points_against'] += $player2Points;
            $standings[$player2Key]['points_for'] += $player2Points;
            $standings[$player2Key]['points_against'] += $player1Points;
            
            // Determine winner
            if ($player1Sets > $player2Sets) {
                $standings[$player1Key]['wins']++;
                $standings[$player2Key]['losses']++;
            } elseif ($player2Sets > $player1Sets) {
                $standings[$player1Key]['losses']++;
                $standings[$player2Key]['wins']++;
            }
        }
        
        // Calculate point differences
        foreach ($standings as $key => $standing) {
            $standings[$key]['point_difference'] = $standing['points_for'] - $standing['points_against'];
        }
        
        // Calculate head-to-head wins for tiebreaking
        $this->calculateHeadToHead($standings, $matches);
        
        // Sort standings by rank criteria
        uasort($standings, function($a, $b) {
            // 1. Wins (descending)
            if ($a['wins'] !== $b['wins']) {
                return $b['wins'] <=> $a['wins'];
            }
            
            // 2. Head-to-head (if they played each other)
            if ($a['head_to_head_wins'] !== $b['head_to_head_wins']) {
                return $b['head_to_head_wins'] <=> $a['head_to_head_wins'];
            }
            
            // 3. Sets won/lost ratio
            $aSetRatio = $a['sets_lost'] > 0 ? $a['sets_won'] / $a['sets_lost'] : $a['sets_won'];
            $bSetRatio = $b['sets_lost'] > 0 ? $b['sets_won'] / $b['sets_lost'] : $b['sets_won'];
            if (abs($aSetRatio - $bSetRatio) > 0.001) {
                return $bSetRatio <=> $aSetRatio;
            }
            
            // 4. Point difference
            if ($a['point_difference'] !== $b['point_difference']) {
                return $b['point_difference'] <=> $a['point_difference'];
            }
            
            // 5. Points for
            return $b['points_for'] <=> $a['points_for'];
        });
        
        // Assign ranks
        $rank = 1;
        foreach ($standings as $key => $standing) {
            $standings[$key]['rank'] = $rank++;
        }
        
        return array_values($standings);
    }
    
    /**
     * Get participant key for a registration (for singles or doubles)
     */
    protected function getParticipantKey(TournamentRegistration $registration): string
    {
        if ($registration->partner_id) {
            // Doubles: use sorted player IDs
            $ids = [$registration->player_id, $registration->partner_id];
            sort($ids);
            return $ids[0] . '_' . $ids[1];
        }
        return (string) $registration->player_id;
    }
    
    /**
     * Get participant key from a match
     */
    protected function getMatchParticipantKey(TournamentMatch $match, string $side): string
    {
        if ($side === 'player1') {
            if ($match->player1_partner_id) {
                $ids = [$match->player1_id, $match->player1_partner_id];
                sort($ids);
                return $ids[0] . '_' . $ids[1];
            }
            return (string) $match->player1_id;
        } else {
            if ($match->player2_partner_id) {
                $ids = [$match->player2_id, $match->player2_partner_id];
                sort($ids);
                return $ids[0] . '_' . $ids[1];
            }
            return (string) $match->player2_id;
        }
    }
    
    /**
     * Get team name for display
     */
    protected function getTeamName(TournamentRegistration $registration): string
    {
        $playerName = $registration->player ? trim(($registration->player->first_name ?? '') . ' ' . ($registration->player->last_name ?? '')) : 'Unknown';
        if ($registration->partner) {
            $partnerName = trim(($registration->partner->first_name ?? '') . ' ' . ($registration->partner->last_name ?? ''));
            return "{$playerName} / {$partnerName}";
        }
        return $playerName;
    }
    
    /**
     * Calculate head-to-head wins for tiebreaking
     */
    protected function calculateHeadToHead(array &$standings, $matches): void
    {
        // For each pair of participants, check if they played each other
        $participantKeys = array_keys($standings);
        
        for ($i = 0; $i < count($participantKeys); $i++) {
            for ($j = $i + 1; $j < count($participantKeys); $j++) {
                $key1 = $participantKeys[$i];
                $key2 = $participantKeys[$j];
                
                // Find matches between these two participants
                foreach ($matches as $match) {
                    $p1Key = $this->getMatchParticipantKey($match, 'player1');
                    $p2Key = $this->getMatchParticipantKey($match, 'player2');
                    
                    if (($p1Key === $key1 && $p2Key === $key2) || ($p1Key === $key2 && $p2Key === $key1)) {
                        $result = $match->result;
                        if (!$result) {
                            continue;
                        }
                        
                        // Calculate sets
                        $p1Sets = 0;
                        $p2Sets = 0;
                        
                        if ($result->player1_set1_score !== null && $result->player2_set1_score !== null) {
                            if ($result->player1_set1_score > $result->player2_set1_score) {
                                $p1Sets++;
                            } else {
                                $p2Sets++;
                            }
                        }
                        if ($result->player1_set2_score !== null && $result->player2_set2_score !== null) {
                            if ($result->player1_set2_score > $result->player2_set2_score) {
                                $p1Sets++;
                            } else {
                                $p2Sets++;
                            }
                        }
                        if ($result->player1_set3_score !== null && $result->player2_set3_score !== null) {
                            if ($result->player1_set3_score > $result->player2_set3_score) {
                                $p1Sets++;
                            } else {
                                $p2Sets++;
                            }
                        }
                        
                        // Determine winner
                        $winnerKey = ($p1Key === $key1) ? ($p1Sets > $p2Sets ? $key1 : $key2) : ($p2Sets > $p1Sets ? $key2 : $key1);
                        
                        if ($winnerKey === $key1) {
                            $standings[$key1]['head_to_head_wins']++;
                        } else {
                            $standings[$key2]['head_to_head_wins']++;
                        }
                        
                        break; // Only count first match (should only be one in round robin)
                    }
                }
            }
        }
    }
}

