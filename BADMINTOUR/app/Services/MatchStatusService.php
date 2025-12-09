<?php

namespace App\Services;

use App\Models\TournamentMatch;
use Carbon\Carbon;

class MatchStatusService
{
    /**
     * Update match statuses based on current date and time
     * 
     * @param TournamentMatch|null $match If provided, only update this match. Otherwise, update all matches.
     * @return void
     */
    public function updateMatchStatuses(?TournamentMatch $match = null): void
    {
        $now = Carbon::now();
        
        if ($match) {
            $this->updateSingleMatchStatus($match, $now);
        } else {
            // Update all matches that are scheduled or ongoing (not completed or cancelled)
            $matches = TournamentMatch::whereIn('status', ['scheduled', 'ongoing'])
                ->whereNotNull('scheduled_date')
                ->whereNotNull('scheduled_time')
                ->get();
            
            foreach ($matches as $match) {
                $this->updateSingleMatchStatus($match, $now);
            }
        }
    }
    
    /**
     * Update status for a single match
     */
    protected function updateSingleMatchStatus(TournamentMatch $match, Carbon $now): void
    {
        if (!$match->scheduled_date || !$match->scheduled_time) {
            // If no schedule, keep as scheduled (default status)
            if ($match->status !== 'scheduled' && $match->status !== 'completed' && $match->status !== 'cancelled') {
                $match->update(['status' => 'scheduled']);
            }
            return;
        }
        
        // Parse scheduled datetime
        // scheduled_time is cast as datetime, so it might be a full datetime or just time
        try {
            $scheduledTime = $match->scheduled_time instanceof Carbon 
                ? $match->scheduled_time 
                : Carbon::parse($match->scheduled_time);
            
            $scheduledDate = $match->scheduled_date instanceof Carbon 
                ? $match->scheduled_date 
                : Carbon::parse($match->scheduled_date);
            
            // Combine date and time
            $scheduledDateTime = $scheduledDate->copy()
                ->setTime($scheduledTime->hour, $scheduledTime->minute, $scheduledTime->second);
        } catch (\Exception $e) {
            // If parsing fails, keep as scheduled
            if ($match->status !== 'scheduled' && $match->status !== 'completed' && $match->status !== 'cancelled') {
                $match->update(['status' => 'scheduled']);
            }
            return;
        }
        
        // Get match duration from category (default 45 minutes for singles, 60 for doubles)
        $category = $match->category;
        $matchDuration = $category ? ($category->match_duration_minutes ?? 45) : 45;
        
        // Calculate match end time
        $matchEndTime = $scheduledDateTime->copy()->addMinutes($matchDuration);
        
        // Determine status based on current time
        if ($match->status === 'completed' || $match->status === 'cancelled') {
            // Don't change completed or cancelled matches
            return;
        }
        
        if ($now->lt($scheduledDateTime)) {
            // Match hasn't started yet
            if ($match->status !== 'scheduled') {
                $match->update(['status' => 'scheduled']);
            }
        } elseif ($now->gte($scheduledDateTime) && $now->lt($matchEndTime)) {
            // Match is currently happening
            if ($match->status !== 'ongoing') {
                $match->update(['status' => 'ongoing']);
            }
        } elseif ($now->gte($matchEndTime)) {
            // Match should have ended (but not marked as completed yet)
            // Only auto-update to completed if it's been more than 2 hours past end time
            // This gives managers time to input results
            $twoHoursAfterEnd = $matchEndTime->copy()->addHours(2);
            if ($now->gte($twoHoursAfterEnd) && $match->status !== 'completed') {
                // Don't auto-complete - let manager mark as completed when results are entered
                // Keep as 'ongoing' or 'scheduled' until manager inputs results
            }
        }
    }
    
    /**
     * Get matches grouped by status for a tournament
     * 
     * @param int $tournamentId
     * @return array
     */
    public function getMatchesByStatus(int $tournamentId): array
    {
        // Get all matches for this tournament first
        // Sort by date and time chronologically
        $matches = TournamentMatch::where('tournament_id', $tournamentId)
            ->with(['player1', 'player2', 'player1Partner', 'player2Partner', 'category', 'result', 'winner', 'winnerPartner'])
            ->get()
            ->sortBy(function($match) {
                if (!$match->scheduled_date || !$match->scheduled_time) {
                    return '9999-12-31 23:59:59'; // Put unscheduled matches at the end
                }
                $date = \Carbon\Carbon::parse($match->scheduled_date)->format('Y-m-d');
                $time = \Carbon\Carbon::parse($match->scheduled_time)->format('H:i:s');
                return $date . ' ' . $time;
            })
            ->values();
        
        // Update statuses for matches that need updating (scheduled or ongoing)
        foreach ($matches as $match) {
            if (in_array($match->status, ['scheduled', 'ongoing']) && $match->scheduled_date && $match->scheduled_time) {
                $this->updateSingleMatchStatus($match, Carbon::now());
                // Refresh the match to get updated status
                $match->refresh();
            }
        }
        
        $now = Carbon::now();
        
        $scheduled = collect([]);
        $ongoing = collect([]);
        $completed = collect([]);
        
        foreach ($matches as $match) {
            if ($match->status === 'completed') {
                $completed->push($match);
            } elseif ($match->status === 'ongoing') {
                $ongoing->push($match);
            } elseif ($match->status === 'scheduled') {
                // Further filter scheduled matches: show as "ongoing" if time has passed
                if ($match->scheduled_date && $match->scheduled_time) {
                    try {
                        // Parse scheduled datetime
                        $scheduledTime = $match->scheduled_time instanceof Carbon 
                            ? $match->scheduled_time 
                            : Carbon::parse($match->scheduled_time);
                        
                        $scheduledDate = $match->scheduled_date instanceof Carbon 
                            ? $match->scheduled_date 
                            : Carbon::parse($match->scheduled_date);
                        
                        // Combine date and time
                        $scheduledDateTime = $scheduledDate->copy()
                            ->setTime($scheduledTime->hour, $scheduledTime->minute, $scheduledTime->second);
                        
                        $category = $match->category;
                        $matchDuration = $category ? ($category->match_duration_minutes ?? 45) : 45;
                        $matchEndTime = $scheduledDateTime->copy()->addMinutes($matchDuration);
                        
                        if ($now->gte($scheduledDateTime) && $now->lt($matchEndTime)) {
                            // Should be ongoing
                            $match->update(['status' => 'ongoing']);
                            $ongoing->push($match);
                        } else {
                            $scheduled->push($match);
                        }
                    } catch (\Exception $e) {
                        // If parsing fails, show as scheduled
                        $scheduled->push($match);
                    }
                } else {
                    // No schedule - show as scheduled
                    $scheduled->push($match);
                }
            } else {
                // Other statuses (cancelled, etc.) - show as scheduled for now
                $scheduled->push($match);
            }
        }
        
        return [
            'scheduled' => $scheduled,
            'ongoing' => $ongoing,
            'completed' => $completed,
        ];
    }
}

