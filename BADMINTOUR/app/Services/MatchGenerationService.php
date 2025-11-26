<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentCategory;
use App\Models\TournamentMatch;
use App\Models\TournamentRegistration;
use Carbon\Carbon;

class MatchGenerationService
{
    public function generateMatches(Tournament $tournament, string $bracketType = 'single_elimination'): void
    {
        foreach ($tournament->categories as $category) {
            $approvedRegistrations = TournamentRegistration::where('tournament_category_id', $category->id)
                ->where('status', 'approved')
                ->get();

            if ($approvedRegistrations->count() < 2) {
                continue;
            }

            match($bracketType) {
                'single_elimination' => $this->generateSingleEliminationBracket($category, $approvedRegistrations, $tournament),
                'double_elimination' => $this->generateDoubleEliminationBracket($category, $approvedRegistrations, $tournament),
                'round_robin' => $this->generateRoundRobinBracket($category, $approvedRegistrations, $tournament),
                default => $this->generateSingleEliminationBracket($category, $approvedRegistrations, $tournament),
            };
        }
    }

    protected function generateSingleEliminationBracket($category, $registrations, $tournament): void
    {
        $participants = $registrations->shuffle();
        $totalParticipants = $participants->count();
        
        $nextPowerOfTwo = pow(2, ceil(log($totalParticipants, 2)));
        $byes = $nextPowerOfTwo - $totalParticipants;

        $round = 1;
        $matchNumber = 1;
        $courts = range(1, $tournament->number_of_courts);
        $courtIndex = 0;

        foreach ($participants->chunk(2) as $pair) {
            if ($pair->count() == 2) {
                TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'tournament_category_id' => $category->id,
                    'round' => $round,
                    'match_number' => $matchNumber++,
                    'player1_id' => $pair[0]->player_id,
                    'player2_id' => $pair[1]->player_id,
                    'player1_partner_id' => $pair[0]->partner_id,
                    'player2_partner_id' => $pair[1]->partner_id,
                    'scheduled_date' => $tournament->start_date,
                    'scheduled_time' => $this->calculateMatchTime($round, $matchNumber),
                    'court_number' => $courts[$courtIndex % count($courts)],
                    'status' => 'scheduled',
                ]);
                $courtIndex++;
            } else {
                $byeMatch = TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'tournament_category_id' => $category->id,
                    'round' => $round,
                    'match_number' => $matchNumber++,
                    'player1_id' => $pair[0]->player_id,
                    'player1_partner_id' => $pair[0]->partner_id,
                    'scheduled_date' => $tournament->start_date,
                    'status' => 'completed',
                    'winner_id' => $pair[0]->player_id,
                    'winner_partner_id' => $pair[0]->partner_id,
                ]);
                
                $this->advanceWinner($byeMatch);
            }
        }
    }

    protected function generateDoubleEliminationBracket($category, $registrations, $tournament): void
    {
        $this->generateSingleEliminationBracket($category, $registrations, $tournament);
    }

    protected function generateRoundRobinBracket($category, $registrations, $tournament): void
    {
        $participants = $registrations->toArray();
        $totalParticipants = count($participants);
        
        $round = 1;
        $matchNumber = 1;

        for ($i = 0; $i < $totalParticipants - 1; $i++) {
            for ($j = $i + 1; $j < $totalParticipants; $j++) {
                TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'tournament_category_id' => $category->id,
                    'round' => $round,
                    'match_number' => $matchNumber++,
                    'player1_id' => $participants[$i]['player_id'],
                    'player2_id' => $participants[$j]['player_id'],
                    'player1_partner_id' => $participants[$i]['partner_id'] ?? null,
                    'player2_partner_id' => $participants[$j]['partner_id'] ?? null,
                    'scheduled_date' => Carbon::parse($tournament->start_date)->addDays($round - 1),
                    'scheduled_time' => $this->calculateMatchTime($round, $matchNumber),
                    'court_number' => (($matchNumber - 1) % $tournament->number_of_courts) + 1,
                    'status' => 'scheduled',
                ]);
            }
            $round++;
        }
    }

    protected function calculateMatchTime(int $round, int $matchNumber): string
    {
        $baseHour = 8;
        $matchDurationMinutes = 45;
        
        $totalMinutes = ($matchNumber - 1) * $matchDurationMinutes;
        $hours = $baseHour + floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        
        return sprintf('%02d:%02d:00', $hours, $minutes);
    }
}