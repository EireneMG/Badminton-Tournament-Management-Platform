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
}