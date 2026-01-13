<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\TournamentMatch;
use App\Models\Tournament;
use App\Models\MatchResult;
use App\Models\Notification;
use App\Services\EloRatingService;
use App\Services\MatchGenerationService;
use App\Services\MatchStatusService;
use App\Services\TournamentStatusService;
use App\Enums\TournamentStatus;
use App\Enums\MatchStatus;
use App\Models\Club;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class MatchController extends Controller
{
    protected $eloRatingService;
    protected $matchGenerationService;
    protected $matchStatusService;
    protected $tournamentStatusService;

    public function __construct(
        EloRatingService $eloRatingService, 
        MatchGenerationService $matchGenerationService, 
        MatchStatusService $matchStatusService,
        TournamentStatusService $tournamentStatusService
    ) {
        $this->eloRatingService = $eloRatingService;
        $this->matchGenerationService = $matchGenerationService;
        $this->matchStatusService = $matchStatusService;
        $this->tournamentStatusService = $tournamentStatusService;
    }
    
    /**
     * Add no-cache headers to response
     */
    protected function addNoCacheHeaders($response)
    {
        $response->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', '0');
        return $response;
    }

    /**
     * Check if the current manager is the tournament host
     * STRICT MODE (Option A): Only the tournament host can manage ALL aspects
     * 
     * @param \App\Models\Tournament $tournament
     * @return bool
     */
    protected function isHostManager(Tournament $tournament): bool
    {
        return $tournament->club && $tournament->club->manager_id === auth()->id();
    }

    public function index(Request $request)
    {
        $club = \App\Models\Club::where('manager_id', auth()->id())->first();
        
        // If manager doesn't have a club yet, show empty state
        if (!$club) {
            return view('manager.matches', [
                'tournaments' => collect([]), 
                'allTournaments' => collect([]),
                'selectedTournament' => null,
                'categories' => collect([]), 
                'club' => null,
                'totalRegistrations' => 0,
                'approvedRegistrations' => 0,
                'participatingClubs' => collect([]),
                'matchesByStatus' => [
                    'scheduled' => collect([]),
                    'ongoing' => collect([]),
                    'completed' => collect([]),
                ],
                'matchesByCategory' => [],
                'selectedCategoryId' => null,
                'isOwner' => false,
            ]);
        }
        
        // Get all tournaments for the club (including dual meet tournaments where this club participates)
        // For dual meet tournaments, we need to check if players from this club are registered
        $tournaments = \App\Models\Tournament::where('club_id', $club->id)
            ->with(['categories'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Also include dual meet tournaments where this club's players are registered
        // (Dual meet tournaments may be hosted by another club but this club participates)
        $dualMeetTournaments = \App\Models\Tournament::where('is_dual_meet', true)
            ->whereHas('registrations', function($query) use ($club) {
                $query->whereHas('player', function($q) use ($club) {
                    $q->whereHas('clubMemberships', function($clubQuery) use ($club) {
                        $clubQuery->where('club_id', $club->id)->where('status', 'approved');
                    });
                });
            })
            ->with(['categories'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Merge and remove duplicates
        $allTournaments = $tournaments->merge($dualMeetTournaments)->unique('id');
        
        // Get selected tournament (from query parameter or first tournament)
        $selectedTournamentId = $request->query('tournament');
        $selectedTournament = null;
        
        if ($selectedTournamentId) {
            // Find tournament in either list
            $selectedTournament = $allTournaments->firstWhere('id', $selectedTournamentId);
            if ($selectedTournament) {
                // Reload with full relationships
                $selectedTournament = \App\Models\Tournament::where('id', $selectedTournamentId)
                    ->with(['categories', 'matches.player1', 'matches.player2', 'matches.player1Partner', 'matches.player2Partner', 'matches.category', 'matches.result', 'matches.winner', 'matches.winnerPartner', 'club'])
                    ->first();
            }
        } elseif ($allTournaments->count() > 0) {
            // Default to first tournament if none selected
            $firstTournamentId = $allTournaments->first()->id;
            $selectedTournament = \App\Models\Tournament::where('id', $firstTournamentId)
                ->with(['categories', 'matches.player1', 'matches.player2', 'matches.player1Partner', 'matches.player2Partner', 'matches.category', 'matches.result', 'matches.winner', 'matches.winnerPartner', 'club'])
                ->first();
        }
        
        // Auto-generate brackets when tournament reaches start date and has approvals
        if ($selectedTournament) {
            $hasRegistrations = $selectedTournament->registrations()->where('status', 'approved')->count() > 0;
            $hasNoMatches = $selectedTournament->matches()->count() === 0;
            $startReached = $selectedTournament->start_date ? \Carbon\Carbon::parse($selectedTournament->start_date)->isPast() : false;
            $isOngoingEligible = in_array($selectedTournament->status, ['upcoming', 'ongoing'], true);

            $shouldGenerate = $isOngoingEligible && $startReached && $hasRegistrations && $hasNoMatches;
            
            if ($shouldGenerate) {
                try {
                    $matchGenerationService = app(\App\Services\MatchGenerationService::class);
                    $bracketType = $selectedTournament->bracket_type ?? 'single_elimination';
                    $matchGenerationService->generateMatches($selectedTournament, $bracketType);
                    // Refresh and mark tournament ongoing if it was upcoming
                    $selectedTournament->refresh();
                    if ($selectedTournament->status === TournamentStatus::UPCOMING->value) {
                        $selectedTournament->status = TournamentStatus::ONGOING->value;
                        $selectedTournament->save();
                    }
                    $selectedTournament->load(['categories', 'matches.player1', 'matches.player2', 'matches.player1Partner', 'matches.player2Partner', 'matches.category', 'matches.result', 'matches.winner', 'matches.winnerPartner', 'club']);
                } catch (\Exception $e) {
                    \Log::error('Failed to auto-generate matches: ' . $e->getMessage());
                    \Log::error('Stack trace: ' . $e->getTraceAsString());
                }
            }
        }
        
        $categories = collect([]);
        $totalRegistrations = 0;
        $approvedRegistrations = 0;
        
        if ($selectedTournament) {
            $categories = $selectedTournament->categories;
            $totalRegistrations = $selectedTournament->registrations()->count();
            $approvedRegistrations = $selectedTournament->registrations()->where('status', 'approved')->count();
        }
        
        // For dual meet tournaments, get participating clubs
        $participatingClubs = collect([]);
        if ($selectedTournament && $selectedTournament->is_dual_meet) {
            $clubIds = \App\Models\TournamentRegistration::where('tournament_id', $selectedTournament->id)
                ->where('status', 'approved')
                ->with(['player.clubMemberships' => function($query) {
                    $query->where('status', 'approved');
                }])
                ->get()
                ->map(function($registration) {
                    $player = $registration->player;
                    if ($player && $player->relationLoaded('clubMemberships')) {
                        $membership = $player->clubMemberships->first();
                        return $membership ? $membership->club_id : null;
                    }
                    return null;
                })
                ->filter()
                ->unique()
                ->toArray();
            
            // Add the hosting club if not already included
            if ($selectedTournament->club_id && !in_array($selectedTournament->club_id, $clubIds)) {
                $clubIds[] = $selectedTournament->club_id;
            }
            
            $participatingClubs = \App\Models\Club::whereIn('id', $clubIds)->get();
        }
        
        // Get matches grouped by status (scheduled, ongoing, completed) and by category/normalized round
        $matchesByStatus = [
            'scheduled' => collect([]),
            'ongoing' => collect([]),
            'completed' => collect([]),
        ];
        $matchesByCategory = []; // Initialize to empty array
        $selectedCategoryId = $request->query('category');
        
        if ($selectedTournament) {
            // Always load matches with relations
            $selectedTournament->load(['matches.player1', 'matches.player2', 'matches.player1Partner', 'matches.player2Partner', 'matches.category', 'matches.result', 'matches.winner', 'matches.winnerPartner']);

            // Build matchesByCategory with normalized rounds (reuse manager controller logic if available)
            $tournamentController = app(\App\Http\Controllers\Manager\TournamentController::class);
            if (method_exists($tournamentController, 'buildMatchesByCategory')) {
                $matchesByCategory = $tournamentController->buildMatchesByCategory($selectedTournament);
            }

            if ($selectedTournament->status === TournamentStatus::COMPLETED->value) {
                $allMatches = TournamentMatch::where('tournament_id', $selectedTournament->id)
                    ->with(['player1', 'player2', 'player1Partner', 'player2Partner', 'category', 'result', 'winner', 'winnerPartner'])
                    ->get()
                    ->sortBy(function($match) {
                        if (!$match->scheduled_date || !$match->scheduled_time) {
                            return '9999-12-31 23:59:59';
                        }
                        $date = \Carbon\Carbon::parse($match->scheduled_date)->format('Y-m-d');
                        $time = \Carbon\Carbon::parse($match->scheduled_time)->format('H:i:s');
                        return $date . ' ' . $time;
                    })
                    ->values();
                
                $matchesByStatus = [
                    'scheduled' => $allMatches->where('status', 'scheduled'),
                    'ongoing' => $allMatches->where('status', 'ongoing'),
                    'completed' => $allMatches->where('status', 'completed'),
                ];
            } else {
                $matchesByStatus = $this->matchStatusService->getMatchesByStatus($selectedTournament->id);
            }
            
            $totalMatchesInDb = TournamentMatch::where('tournament_id', $selectedTournament->id)->count();
            $totalInStatus = $matchesByStatus['scheduled']->count() + $matchesByStatus['ongoing']->count() + $matchesByStatus['completed']->count();
            if ($totalMatchesInDb > 0 && $totalInStatus === 0) {
                \Log::warning("Matches exist in DB ({$totalMatchesInDb}) but matchesByStatus is empty for tournament {$selectedTournament->id}");
            }
        }
        
        // Check if manager owns the tournament
        $isOwner = $selectedTournament && $selectedTournament->club && $selectedTournament->club->manager_id === auth()->id();
        
        // Check for matches needing results and send reminder notifications
        if ($selectedTournament && $isOwner) {
            $this->checkMatchesNeedingResults($selectedTournament);
        }
        
        return view('manager.matches', compact(
            'tournaments',
            'allTournaments',
            'selectedTournament',
            'categories',
            'club',
            'totalRegistrations',
            'approvedRegistrations',
            'participatingClubs',
            'matchesByStatus',
            'matchesByCategory',
            'selectedCategoryId',
            'isOwner'
        ));
    }

    /**
     * Check for matches that need results and send reminder notifications
     */
    protected function checkMatchesNeedingResults($tournament): void
    {
        $now = Carbon::now();
        $matchesNeedingResults = TournamentMatch::where('tournament_id', $tournament->id)
            ->whereIn('status', ['scheduled', 'ongoing'])
            ->whereNotNull('scheduled_date')
            ->whereNotNull('scheduled_time')
            ->whereNotNull('player1_id')
            ->whereNotNull('player2_id')
            ->with(['category', 'result'])
            ->get()
            ->filter(function($match) use ($now) {
                // Match must not have a result
                if ($match->result) {
                    return false;
                }
                
                if (!$match->scheduled_date || !$match->scheduled_time) {
                    return false;
                }
                
                $matchDateTime = Carbon::parse($match->scheduled_date)
                    ->setTimeFromTimeString($match->scheduled_time);
                $category = $match->category;
                $matchDuration = $category ? ($category->match_duration_minutes ?? 45) : 45;
                $matchEndTime = $matchDateTime->copy()->addMinutes($matchDuration);
                
                // Send reminder if match ended more than 1 hour ago and no result entered
                return $now->isAfter($matchEndTime->copy()->addHour());
            });

        // Send notification for matches needing results (once per day per tournament)
        if ($matchesNeedingResults->count() > 0) {
            $lastNotification = \App\Models\Notification::where('user_id', auth()->id())
                ->where('type', 'matches_need_results')
                ->where('data->tournament_id', $tournament->id)
                ->whereDate('created_at', Carbon::today())
                ->first();

            if (!$lastNotification) {
                Notification::create([
                    'user_id' => auth()->id(),
                    'type' => 'matches_need_results',
                    'title' => 'Matches Need Results',
                    'message' => "You have {$matchesNeedingResults->count()} match(es) in {$tournament->name} that need results entered. Please input the results to update brackets and rankings.",
                    'data' => [
                        'tournament_id' => $tournament->id,
                        'match_count' => $matchesNeedingResults->count(),
                    ],
                    'action_url' => route('manager.matches', ['tournament' => $tournament->id]),
                ]);
            }
        }
    }

    public function updateScore(TournamentMatch $match, Request $request): RedirectResponse
    {
        // STRICT MODE (Option A): Only the tournament host manager can input match scores
        if (!$this->isHostManager($match->tournament)) {
            abort(403, 'Unauthorized: Only the tournament host manager can input match scores. This is a dual meet tournament - the host will handle all match management.');
        }
        
        // Validate that both players are assigned (no TBD slots)
        if (!$match->player1_id || !$match->player2_id) {
            return back()->with('error', 'Cannot enter result: Match does not have both players assigned yet. Please wait for previous round to complete.');
        }
        
        $request->validate([
            'player1_score' => ['required', 'integer', 'min:0'],
            'player2_score' => ['required', 'integer', 'min:0'],
            'set1_player1' => ['nullable', 'integer', 'min:0'],
            'set1_player2' => ['nullable', 'integer', 'min:0'],
            'set2_player1' => ['nullable', 'integer', 'min:0'],
            'set2_player2' => ['nullable', 'integer', 'min:0'],
            'set3_player1' => ['nullable', 'integer', 'min:0'],
            'set3_player2' => ['nullable', 'integer', 'min:0'],
        ]);
        
        $winnerId = $request->player1_score > $request->player2_score 
            ? $match->player1_id 
            : $match->player2_id;
        
        $winnerPartnerId = $winnerId === $match->player1_id 
            ? $match->player1_partner_id 
            : $match->player2_partner_id;
        
        // Create or update match result
        // Note: elo_updated will be set by MatchScoreService after ELO calculation
        $resultData = [
            'player1_score' => $request->player1_score,
            'player2_score' => $request->player2_score,
            'set1_player1' => $request->set1_player1,
            'set1_player2' => $request->set1_player2,
            'set2_player1' => $request->set2_player1,
            'set2_player2' => $request->set2_player2,
            'set3_player1' => $request->set3_player1,
            'set3_player2' => $request->set3_player2,
            'winner_id' => $winnerId,
        ];
        
        // Only include elo_updated if column exists (for backward compatibility)
        if (Schema::hasColumn('match_results', 'elo_updated')) {
            $resultData['elo_updated'] = false;
        }
        
        $result = MatchResult::updateOrCreate(
            ['match_id' => $match->id],
            $resultData
        );
        
        $match->update([
            'status' => 'completed',
            'winner_id' => $winnerId,
            'winner_partner_id' => $winnerPartnerId,
        ]);
        
        $category = $match->category;
        $player1 = $match->player1;
        $player2 = $match->player2;
        
        // Validate players exist before processing
        if (!$player1 || !$player2) {
            return back()->with('error', 'Cannot process result: One or more players not found. Match must have both players assigned.');
        }
        
        // Use MatchScoreService to process result and update ELO (consolidated logic)
        $matchScoreService = app(\App\Services\MatchScoreService::class);
        $result = MatchResult::where('match_id', $match->id)->first();
        
        if ($result) {
            $eloResult = $matchScoreService->processMatchResultAndUpdateElo($match, $result);
            if (!$eloResult['success']) {
                \Log::warning('ELO update failed: ' . $eloResult['message'], ['match_id' => $match->id]);
            }
        } else {
            // Fallback: Update ELO directly if result doesn't exist yet (shouldn't happen)
            if (in_array($category->type, ['MD', 'WD', 'XD']) && $match->player1_partner_id && $match->player2_partner_id) {
                $player1Partner = $match->player1Partner;
                $player2Partner = $match->player2Partner;
                
                if (!$player1Partner || !$player2Partner) {
                    return back()->with('error', 'Cannot process doubles result: One or more partners not found.');
                }
                
                $this->eloRatingService->calculateDoublesMatchRatings(
                    $player1, 
                    $player1Partner, 
                    $player2, 
                    $player2Partner,
                    $winnerId === $player1->id,
                    $category->type,
                    $match->tournament_id
                );
            } else {
                $this->eloRatingService->calculateMatchRatings(
                    $player1,
                    $player2,
                    $winnerId === $player1->id,
                    $category->type,
                    $match->tournament_id
                );
            }
        }
        
        $this->matchGenerationService->advanceWinner($match);
        
        $statisticsService = app(\App\Services\StatisticsService::class);
        $statisticsService->invalidatePlayerCache($player1);
        $statisticsService->invalidatePlayerCache($player2);
        
        if ($match->player1_partner_id) {
            $statisticsService->invalidatePlayerCache($match->player1Partner);
        }
        if ($match->player2_partner_id) {
            $statisticsService->invalidatePlayerCache($match->player2Partner);
        }
        
        if ($match->tournament && $match->tournament->club) {
            $statisticsService->invalidateClubCache($match->tournament->club);
            if ($match->tournament->club->manager) {
                $statisticsService->invalidateManagerCache($match->tournament->club->manager);
            }
        }
        
        // Send notifications to all players (including partners for doubles)
        $playersToNotify = [];
        
        if ($player1) {
            $playersToNotify[] = $player1->id;
        }
        if ($player2) {
            $playersToNotify[] = $player2->id;
        }
        
        // Add partners for doubles/mixed doubles matches
        if ($match->player1_partner_id) {
            $playersToNotify[] = $match->player1_partner_id;
        }
        if ($match->player2_partner_id) {
            $playersToNotify[] = $match->player2_partner_id;
        }
        
        // Remove duplicates and send notifications
        $playersToNotify = array_unique(array_filter($playersToNotify));
        foreach ($playersToNotify as $playerId) {
            try {
                Notification::create([
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
        
        // Check if this is a late result input
        $isLateInput = false;
        $lateWarning = '';
        
        if ($match->scheduled_date && $match->scheduled_time) {
            $matchDateTime = \Carbon\Carbon::parse($match->scheduled_date)
                ->setTimeFromTimeString($match->scheduled_time);
            $category = $match->category;
            $matchDuration = $category ? ($category->match_duration_minutes ?? 45) : 45;
            $matchEndTime = $matchDateTime->copy()->addMinutes($matchDuration);
            
            if (\Carbon\Carbon::now()->isAfter($matchEndTime)) {
                $isLateInput = true;
                $hoursLate = (int)\Carbon\Carbon::now()->diffInHours($matchEndTime);
                $daysLate = (int)\Carbon\Carbon::now()->diffInDays($matchEndTime);
                if ($daysLate > 0) {
                    $lateWarning = "This result is being entered {$daysLate} day(s) after the match was scheduled to end.";
                } else {
                    $lateWarning = "This result is being entered {$hoursLate} hour(s) after the match was scheduled to end.";
                }
            }
        }

        // Check if next round match has already started/completed
        $nextMatchWarning = '';
        $category = $match->category;
        $slots = $category->max_participants ?? 16;
        $rounds = app(\App\Services\CategoryScheduleService::class)->calculateRoundsForBracket($slots, $match->tournament->bracket_type ?? 'single_elimination');
        
        $currentRoundIndex = -1;
        foreach ($rounds as $index => $roundInfo) {
            if ($roundInfo['name'] === $match->round) {
                $currentRoundIndex = $index;
                break;
            }
        }
        
        if ($currentRoundIndex >= 0 && $currentRoundIndex < count($rounds) - 1) {
            $nextRoundName = $rounds[$currentRoundIndex + 1]['name'];
            $nextMatch = TournamentMatch::where('tournament_id', $match->tournament_id)
                ->where('tournament_category_id', $match->tournament_category_id)
                ->where('round', $nextRoundName)
                ->where(function($query) use ($match) {
                    $query->where('player1_id', $match->player1_id)
                          ->orWhere('player2_id', $match->player1_id)
                          ->orWhere('player1_id', $match->player2_id)
                          ->orWhere('player2_id', $match->player2_id);
                })
                ->first();
            
            if ($nextMatch && $nextMatch->status === MatchStatus::COMPLETED->value) {
                $nextMatchWarning = "Note: The next round match has already been completed. This result will still update brackets and ELO ratings.";
            } elseif ($nextMatch && $nextMatch->status === MatchStatus::ONGOING->value) {
                $nextMatchWarning = "Note: The next round match has already started. This result will still update brackets and ELO ratings.";
            }
        }

        // Build success message with warnings if any
        $successMessage = 'Match score updated and winner advanced successfully!';
        if ($isLateInput || $nextMatchWarning) {
            $warnings = array_filter([$lateWarning, $nextMatchWarning]);
            if (!empty($warnings)) {
                $successMessage .= ' ' . implode(' ', $warnings);
            }
        }

        return back()->with('success', $successMessage);
    }

    /**
     * Check if manager can manage matches in this tournament
     * For dual meet tournaments: Tournament owner OR any participating club manager
     * For regular tournaments: Only tournament owner
     */
    protected function canManageTournamentMatches(\App\Models\Tournament $tournament): bool
    {
        $managerId = auth()->id();
        
        // Tournament owner can always manage
        if ($tournament->club && $tournament->club->manager_id === $managerId) {
            return true;
        }
        
        // For dual meet tournaments, check if manager's club is participating
        if ($tournament->is_dual_meet) {
            $managerClub = Club::where('manager_id', $managerId)->first();
            if (!$managerClub) {
                return false;
            }
            
            // Check if any players from this manager's club are registered in the tournament
            $hasParticipatingPlayers = \App\Models\TournamentRegistration::where('tournament_id', $tournament->id)
                ->where('status', 'approved')
                ->whereHas('player', function($query) use ($managerClub) {
                    $query->whereHas('clubMemberships', function($q) use ($managerClub) {
                        $q->where('club_id', $managerClub->id)->where('status', 'approved');
                    });
                })
                ->exists();
            
            return $hasParticipatingPlayers;
        }
        
        return false;
    }

    public function markWalkover(TournamentMatch $match, Request $request): RedirectResponse
    {
        // STRICT MODE (Option A): Only the tournament host manager can mark walkovers
        if (!$this->isHostManager($match->tournament)) {
            abort(403, 'Unauthorized: Only the tournament host manager can manage matches. This is a dual meet tournament - the host will handle all match management.');
        }

        // Check if confirmation is provided (prevents accidental marking)
        if (!$request->has('confirmed') || !$request->boolean('confirmed')) {
            return back()->with('error', 'Please confirm the walkover action.');
        }

        $request->validate([
            'winner_id' => ['required', 'exists:users,id'],
            'confirmed' => ['required', 'boolean'],
        ]);

        // Wrap everything in a database transaction to ensure atomicity
        return DB::transaction(function () use ($request, $match) {
            // Refresh match to get latest data
            $match = $match->fresh();
            
            $winnerId = $request->winner_id;
            $losingTeamPlayerIds = [];
            
            // Determine winner and loser information
            $winnerPartnerId = null;
            if ($match->player1_id == $winnerId) {
                $winnerPartnerId = $match->player1_partner_id;
                // Loser is player2 and their partner
                $losingTeamPlayerIds[] = $match->player2_id;
                if ($match->player2_partner_id) {
                    $losingTeamPlayerIds[] = $match->player2_partner_id;
                }
            } elseif ($match->player2_id == $winnerId) {
                $winnerPartnerId = $match->player2_partner_id;
                // Loser is player1 and their partner
                $losingTeamPlayerIds[] = $match->player1_id;
                if ($match->player1_partner_id) {
                    $losingTeamPlayerIds[] = $match->player1_partner_id;
                }
            }

            // Mark match as completed with walkover
            // IMPORTANT: Set status to 'completed' explicitly to prevent MatchStatusService from overriding
            $match->update([
                'status' => 'completed',
                'winner_id' => $winnerId,
                'winner_partner_id' => $winnerPartnerId,
            ]);
            $match->refresh(); // Ensure status is persisted

            // Create a minimal match result record for walkover (no scores, just winner)
            MatchResult::updateOrCreate(
                ['match_id' => $match->id],
                [
                    'player1_set1_score' => 0,
                    'player2_set1_score' => 0,
                    'winner_id' => $winnerId,
                    'score_inputted_by' => 'manager',
                    'inputted_by_user_id' => auth()->id(),
                    'is_walkover' => true,
                ]
            );

            // Apply ELO penalty for walkover loss
            // This discourages walkover losses and maintains ranking integrity
            $categoryType = $match->category->type ?? 'MS';
            $walkoverPenalty = \App\Services\EloRatingService::getWalkoverPenalty();
            
            foreach ($losingTeamPlayerIds as $losingPlayerId) {
                if ($losingPlayerId) {
                    try {
                        $losingPlayer = \App\Models\User::find($losingPlayerId);
                        if ($losingPlayer) {
                            $penaltyResult = $this->eloRatingService->applyWalkoverPenalty(
                                $losingPlayer, 
                                $categoryType
                            );
                            
                            \Log::info("Walkover penalty applied to player {$losingPlayerId}: " . json_encode($penaltyResult));
                        }
                    } catch (\Exception $e) {
                        \Log::warning("Failed to apply walkover penalty to player {$losingPlayerId}: " . $e->getMessage());
                    }
                }
            }

            // Advance winner to next round
            // This updates brackets and round progression correctly for all categories
            $this->matchGenerationService->advanceWinner($match);

            // Check and update tournament completion status
            // This ensures tournament is marked as completed when all matches are done
            $this->tournamentStatusService->checkAndUpdateCompletionStatus($match->tournament);

            // Send notifications to all players (including partners for doubles)
            $playersToNotify = [];
            
            if ($match->player1) {
                $playersToNotify[] = $match->player1->id;
            }
            if ($match->player2) {
                $playersToNotify[] = $match->player2->id;
            }
            
            // Add partners for doubles/mixed doubles matches
            if ($match->player1_partner_id) {
                $playersToNotify[] = $match->player1_partner_id;
            }
            if ($match->player2_partner_id) {
                $playersToNotify[] = $match->player2_partner_id;
            }
            
            // Remove duplicates and send notifications
            $playersToNotify = array_unique(array_filter($playersToNotify));
            foreach ($playersToNotify as $playerId) {
                try {
                    $isLoser = in_array($playerId, $losingTeamPlayerIds);
                    $notificationMessage = $isLoser 
                        ? "Your match in {$match->tournament->name} has been marked as a walkover loss. You have received a -25 ELO penalty."
                        : "Your match in {$match->tournament->name} has been marked as a walkover. You advance to the next round.";
                    
                    Notification::create([
                        'user_id' => $playerId,
                        'type' => 'match_walkover',
                        'title' => 'Match Walkover',
                        'message' => $notificationMessage,
                        'data' => ['match_id' => $match->id],
                        'action_url' => route('player.matches.show', $match->id),
                    ]);
                } catch (\Exception $e) {
                    // Log error but don't fail the entire operation
                    \Log::warning("Failed to send notification to player {$playerId}: " . $e->getMessage());
                }
            }

            return back()->with('success', 'Match marked as walkover. Winner advanced to next round. Loser received -25 ELO penalty.');
        });
    }

    public function reschedule(TournamentMatch $match, Request $request): RedirectResponse
    {
        // STRICT MODE (Option A): Only the tournament host manager can reschedule matches
        if (!$this->isHostManager($match->tournament)) {
            abort(403, 'Unauthorized: Only the tournament host manager can reschedule matches. This is a dual meet tournament - the host will handle all match management.');
        }
        
        if ($match->reschedule_count >= 1) {
            return back()->with('error', 'This match has already been rescheduled once. No further rescheduling allowed.');
        }
        
        $request->validate([
            'scheduled_date' => ['required', 'date'],
            'scheduled_time' => ['required'],
            'court_number' => ['required', 'integer', 'min:1'],
        ]);
        
        $newDate = Carbon::parse($request->scheduled_date);
        $tournamentStartDate = Carbon::parse($match->tournament->start_date);
        $tournamentDay = $tournamentStartDate->diffInDays($newDate) + 1;
        
        if ($tournamentDay < 1) {
            $tournamentDay = 1;
        }
        
        $match->update([
            'scheduled_date' => $newDate,
            'scheduled_time' => $request->scheduled_time,
            'tournament_day' => $tournamentDay,
            'court_number' => $request->court_number,
            'reschedule_count' => $match->reschedule_count + 1,
            'status' => 'scheduled',
        ]);
        
        // Update match status based on new schedule
        $this->matchStatusService->updateMatchStatuses($match);
        
        // Send notifications to all players (including partners for doubles)
        $playersToNotify = [];
        
        if ($match->player1_id) {
            $playersToNotify[] = $match->player1_id;
        }
        if ($match->player2_id) {
            $playersToNotify[] = $match->player2_id;
        }
        
        // Add partners for doubles/mixed doubles matches
        if ($match->player1_partner_id) {
            $playersToNotify[] = $match->player1_partner_id;
        }
        if ($match->player2_partner_id) {
            $playersToNotify[] = $match->player2_partner_id;
        }
        
        // Remove duplicates and send notifications
        $playersToNotify = array_unique(array_filter($playersToNotify));
        foreach ($playersToNotify as $playerId) {
            try {
                $notification = Notification::create([
                    'user_id' => $playerId,
                    'type' => 'match_rescheduled',
                    'title' => 'Match Rescheduled',
                    'message' => "Your match in {$match->tournament->name} has been rescheduled.",
                    'data' => ['match_id' => $match->id],
                    'action_url' => route('player.matches.show', $match->id),
                ]);

                // Send email notification
                app(\App\Services\EmailService::class)->sendNotificationEmail($notification);
            } catch (\Exception $e) {
                // Log error but don't fail the entire operation
                \Log::warning("Failed to send notification to player {$playerId}: " . $e->getMessage());
            }
        }
        
        return back()->with('success', 'Match rescheduled successfully!');
    }
}
