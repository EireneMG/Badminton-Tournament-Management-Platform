<?php

namespace App\Services;

use App\Models\TournamentRegistration;
use App\Models\TournamentCategory;
use App\Models\ClubPlayer;
use App\Services\EloRatingService;

class PartnerMatchingService
{
    protected EloRatingService $eloService;

    // Maximum allowable ELO gap for auto-matching (soft cap)
    protected int $maxEloGap;

    public function __construct(EloRatingService $eloService)
    {
        $this->eloService = $eloService;
        $this->maxEloGap = (int) config('elo.gap', env('ELO_FAIR_GAP', 350));
    }

    /**
    * Attempt to auto-assign a partner for a doubles/mixed registration.
    * Returns true if a partner was assigned; false otherwise.
    */
    public function attemptAutoPartner(TournamentRegistration $registration): bool
    {
        $category = $registration->category;
        $tournament = $registration->tournament;

        if (!$category || !$tournament) {
            return false;
        }

        // Only run for doubles / mixed
        $categoryName = strtolower($category->name ?? '');
        $isDoubles = str_contains($categoryName, 'doubles') || str_contains($categoryName, 'mixed') || in_array($category->type, ['MD', 'WD', 'XD']);
        if (!$isDoubles) {
            return false;
        }

        // Already has partner
        if ($registration->partner_id) {
            return false;
        }

        $player = $registration->player;
        if (!$player) {
            return false;
        }

        $playerGender = $player->gender;
        $playerClub = ClubPlayer::where('player_id', $player->id)->where('status', 'approved')->first();

        $playerElo = $this->eloService->getCurrentRating($player, $category->type ?? 'MD');

        // Build candidate pool: same tournament & category, no partner, not withdrawn, not self
        $candidates = TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('category_id', $category->id)
            ->whereNull('partner_id')
            ->where('player_id', '!=', $player->id)
            ->whereIn('status', ['pending', 'eligible', 'approved'])
            ->with('player')
            ->get();

        // Filter by gender rules
        $isMixed = str_contains($categoryName, 'mixed') || $category->type === 'XD';
        $isWomen = str_contains($categoryName, 'women') || $category->type === 'WD';
        $isMen = str_contains($categoryName, 'men') || $category->type === 'MD';

        $candidates = $candidates->filter(function ($cand) use ($isMixed, $isWomen, $isMen, $playerGender, $playerClub, $tournament) {
            $candPlayer = $cand->player;
            if (!$candPlayer) {
                return false;
            }

            if ($isMixed) {
                // Mixed requires opposite genders (gender stored as 'Male'/'Female')
                return $candPlayer->gender !== $playerGender;
            }
            if ($isWomen) {
                return $candPlayer->gender === 'Female';
            }
            if ($isMen) {
                return $candPlayer->gender === 'Male';
            }

            return true;
        });

        // Interclub (dual meet) rule: partners must be from different clubs
        if ($tournament->is_dual_meet && $playerClub) {
            $candidates = $candidates->filter(function ($cand) use ($playerClub) {
                $candClub = ClubPlayer::where('player_id', $cand->player_id)->where('status', 'approved')->first();
                return $candClub && $candClub->club_id !== $playerClub->club_id;
            });
        }

        if ($candidates->isEmpty()) {
            return false;
        }

        // Pick the closest ELO within tolerance
        $bestCandidate = null;
        $bestGap = PHP_INT_MAX;

        foreach ($candidates as $cand) {
            $candElo = $this->eloService->getCurrentRating($cand->player, $category->type ?? 'MD');
            $gap = abs($candElo - $playerElo);

            if ($gap < $bestGap && $gap <= $this->maxEloGap) {
                $bestGap = $gap;
                $bestCandidate = $cand;
            }
        }

        if (!$bestCandidate) {
            return false; // No candidate within tolerance
        }

        // Assign partners both ways
        $registration->partner_id = $bestCandidate->player_id;
        $registration->save();
        $registration->refresh();

        $bestCandidate->partner_id = $player->id;
        $bestCandidate->save();
        $bestCandidate->refresh();

        return true;
    }
}

