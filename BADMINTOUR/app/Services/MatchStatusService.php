<?php

namespace App\Services;

use App\Models\TournamentMatch;
use App\Enums\MatchStatus;
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
            $matches = TournamentMatch::whereIn('status', [MatchStatus::SCHEDULED->value, MatchStatus::ONGOING->value])
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
        if (!$match->scheduled_time) {
            if ($match->status !== MatchStatus::SCHEDULED->value && $match->status !== MatchStatus::COMPLETED->value && $match->status !== MatchStatus::CANCELLED->value) {
                $match->update(['status' => MatchStatus::SCHEDULED->value]);
            }
            return;
        }
        
        try {
            $scheduledDateTime = $match->actual_scheduled_date_time;
            
            if (!$scheduledDateTime) {
                if ($match->status !== 'scheduled' && $match->status !== 'completed' && $match->status !== 'cancelled') {
                    $match->update(['status' => 'scheduled']);
                }
                return;
            }
        } catch (\Exception $e) {
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
        if ($match->status === MatchStatus::COMPLETED->value || $match->status === MatchStatus::CANCELLED->value) {
            return;
        }
        
        if ($now->lt($scheduledDateTime)) {
            if ($match->status !== MatchStatus::SCHEDULED->value) {
                $match->update(['status' => MatchStatus::SCHEDULED->value]);
            }
        } elseif ($now->gte($scheduledDateTime) && $now->lt($matchEndTime)) {
            if ($match->status !== MatchStatus::ONGOING->value) {
                $match->update(['status' => MatchStatus::ONGOING->value]);
            }
        } elseif ($now->gte($matchEndTime)) {
            if ($match->status !== MatchStatus::ONGOING->value && $match->status !== MatchStatus::COMPLETED->value) {
                $match->update(['status' => MatchStatus::ONGOING->value]);
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
                try {
                    $dateTime = $match->actual_scheduled_date_time;
                    if (!$dateTime) {
                        return '9999-12-31 23:59:59';
                    }
                    return $dateTime->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    return '9999-12-31 23:59:59';
                }
            })
            ->values();
        
        // Update statuses for matches that need updating (scheduled or ongoing)
        // This ensures matches are properly marked as ongoing when their scheduled time has passed
        // IMPORTANT: Never update matches that are already completed or cancelled
        foreach ($matches as $match) {
            if (in_array($match->status, ['scheduled', 'ongoing']) && $match->scheduled_time) {
                $this->updateSingleMatchStatus($match, Carbon::now());
                $match->refresh();
            }
        }
        
        foreach ($matches as $match) {
                    if ($match->status === MatchStatus::SCHEDULED->value && $match->scheduled_time) {
                        try {
                            $scheduledDateTime = $match->actual_scheduled_date_time;
                            if ($scheduledDateTime && Carbon::now()->gte($scheduledDateTime)) {
                                $match->update(['status' => MatchStatus::ONGOING->value]);
                                $match->refresh();
                            }
                        } catch (\Exception $e) {
                        }
                    }
        }
        
        $now = Carbon::now();
        
        $scheduled = collect([]);
        $ongoing = collect([]);
        $completed = collect([]);
        
        foreach ($matches as $match) {
            if ($match->status === MatchStatus::COMPLETED->value) {
                $completed->push($match);
            } elseif ($match->status === MatchStatus::ONGOING->value) {
                $ongoing->push($match);
            } elseif ($match->status === MatchStatus::SCHEDULED->value) {
                if ($match->scheduled_time) {
                    try {
                        $scheduledDateTime = $match->actual_scheduled_date_time;
                        if ($scheduledDateTime) {
                            $category = $match->category;
                            $matchDuration = $category ? ($category->match_duration_minutes ?? 45) : 45;
                            $matchEndTime = $scheduledDateTime->copy()->addMinutes($matchDuration);
                            
                            if ($now->gte($scheduledDateTime) && $now->lt($matchEndTime)) {
                                $match->update(['status' => MatchStatus::ONGOING->value]);
                                $ongoing->push($match);
                            } else {
                                $scheduled->push($match);
                            }
                        } else {
                            $scheduled->push($match);
                        }
                    } catch (\Exception $e) {
                        $scheduled->push($match);
                    }
                } else {
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

