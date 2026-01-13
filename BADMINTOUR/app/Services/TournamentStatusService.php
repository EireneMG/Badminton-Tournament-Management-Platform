<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Enums\TournamentStatus;
use App\Enums\MatchStatus;
use Carbon\Carbon;

class TournamentStatusService
{
    /**
     * Update tournament statuses based on dates and match completion
     * 
     * @param Tournament|null $tournament If provided, only update this tournament. Otherwise, update all tournaments.
     * @return void
     */
    public function updateTournamentStatuses(?Tournament $tournament = null): void
    {
        $now = Carbon::now();
        
        if ($tournament) {
            $this->updateSingleTournamentStatus($tournament, $now);
        } else {
            // Update all tournaments that are not completed or cancelled
            $tournaments = Tournament::whereNotIn('status', [TournamentStatus::COMPLETED->value, TournamentStatus::CANCELLED->value])
                ->get();
            
            foreach ($tournaments as $tournament) {
                $this->updateSingleTournamentStatus($tournament, $now);
            }
        }
    }
    
    /**
     * Update status for a single tournament
     */
    protected function updateSingleTournamentStatus(Tournament $tournament, Carbon $now): void
    {
        // Don't update completed or cancelled tournaments
        if (in_array($tournament->status, [TournamentStatus::COMPLETED->value, TournamentStatus::CANCELLED->value])) {
            return;
        }
        
        $startDate = Carbon::parse($tournament->start_date)->startOfDay();
        $endDate = Carbon::parse($tournament->end_date)->endOfDay();
        $registrationDeadline = $tournament->registration_deadline ? Carbon::parse($tournament->registration_deadline)->endOfDay() : null;
        
        // Transition: Published → Upcoming
        // When registration deadline has passed (if set) OR when we're 3 days before start_date
        if ($tournament->status === TournamentStatus::PUBLISHED->value) {
            $shouldBecomeUpcoming = false;
            
            if ($registrationDeadline && $now->gt($registrationDeadline)) {
                $shouldBecomeUpcoming = true;
            }
            elseif ($now->gte($startDate->copy()->subDays(3))) {
                $shouldBecomeUpcoming = true;
            }
            
            if ($shouldBecomeUpcoming) {
                $tournament->update(['status' => TournamentStatus::UPCOMING->value]);
                \Log::info("Tournament status updated: {$tournament->name} (ID: {$tournament->id}) - Published → Upcoming");
                $this->invalidateTournamentStatisticsCache($tournament);
            }
        }
        
        if ($tournament->status === TournamentStatus::UPCOMING->value) {
            if ($now->gte($startDate) && $now->lte($endDate)) {
                $tournament->update(['status' => TournamentStatus::ONGOING->value]);
                \Log::info("Tournament status updated: {$tournament->name} (ID: {$tournament->id}) - Upcoming → Ongoing");
                $this->invalidateTournamentStatisticsCache($tournament);
            }
        }
        
        if ($tournament->status === TournamentStatus::PUBLISHED->value) {
            if ($now->gte($startDate) && $now->lte($endDate)) {
                $tournament->update(['status' => TournamentStatus::ONGOING->value]);
                \Log::info("Tournament status updated: {$tournament->name} (ID: {$tournament->id}) - Published → Ongoing");
                $this->invalidateTournamentStatisticsCache($tournament);
            }
        }
        
        if ($tournament->status === TournamentStatus::ONGOING->value) {
            $allMatches = TournamentMatch::where('tournament_id', $tournament->id)->get();
            
            if ($allMatches->count() > 0) {
                $allMatchesCompleted = $allMatches->every(function($match) {
                    return $match->status === MatchStatus::COMPLETED->value;
                });
                
                if ($allMatchesCompleted) {
                    $tournament->update(['status' => TournamentStatus::COMPLETED->value]);
                    \Log::info("Tournament status updated: {$tournament->name} (ID: {$tournament->id}) - Ongoing → Completed (all matches completed)");
                    return;
                }
            }
            
            if ($now->gt($endDate) && $allMatches->count() === 0) {
                $tournament->update(['status' => TournamentStatus::COMPLETED->value]);
                \Log::info("Tournament status updated: {$tournament->name} (ID: {$tournament->id}) - Ongoing → Completed (end date passed, no matches)");
                $this->invalidateTournamentStatisticsCache($tournament);
            }
        }
    }
    
    /**
     * Check if a tournament should be marked as completed
     * This is called when a match result is recorded
     */
    public function checkAndUpdateCompletionStatus(Tournament $tournament): void
    {
        // Only check if tournament is ongoing
        if ($tournament->status !== TournamentStatus::ONGOING->value) {
            return;
        }
        
        $allMatches = TournamentMatch::where('tournament_id', $tournament->id)->get();
        
        if ($allMatches->count() > 0) {
            $allMatchesCompleted = $allMatches->every(function($match) {
                return $match->status === MatchStatus::COMPLETED->value;
            });
            
            if ($allMatchesCompleted) {
                $tournament->update(['status' => TournamentStatus::COMPLETED->value]);
                \Log::info("Tournament status updated: {$tournament->name} (ID: {$tournament->id}) - Ongoing → Completed (all matches completed)");
                $this->invalidateTournamentStatisticsCache($tournament);
            }
        }
    }

    protected function invalidateTournamentStatisticsCache(Tournament $tournament): void
    {
        try {
            $statisticsService = app(\App\Services\StatisticsService::class);
            
            if ($tournament->club) {
                $statisticsService->invalidateClubCache($tournament->club);
                
                if ($tournament->club->manager) {
                    $statisticsService->invalidateManagerCache($tournament->club->manager);
                }
            }
        } catch (\Exception $e) {
            \Log::warning("Failed to invalidate statistics cache for tournament {$tournament->id}: " . $e->getMessage());
        }
    }
}

