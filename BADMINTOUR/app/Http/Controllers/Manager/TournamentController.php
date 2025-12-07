<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTournamentRequest;
use App\Http\Requests\UpdateTournamentRequest;
use App\Models\Tournament;
use App\Models\TournamentCategory;
use App\Models\Club;
use App\Models\Notification;
use App\Models\TournamentMatch;
use App\Models\MatchResult;
use App\Services\MatchGenerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TournamentController extends Controller
{
    protected $matchGenerationService;

    public function __construct(MatchGenerationService $matchGenerationService)
    {
        $this->matchGenerationService = $matchGenerationService;
    }

    public function index(): View
    {
        $club = Club::where('manager_id', auth()->id())->first();
        
        // Get ALL tournaments for Upcoming, Ongoing, Completed tabs
        $allTournaments = Tournament::with('categories', 'club')
            ->orderBy('start_date', 'desc')
            ->get();
        
        // Get only manager's tournaments for "Your Tournaments" tab
        $myTournaments = collect([]);
        if ($club) {
            $myTournaments = Tournament::where('club_id', $club->id)
                ->with('categories')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('manager.tournaments', compact('allTournaments', 'myTournaments', 'club'));
    }

    public function create(Request $request): View
    {
        // Check if manager is verified
        if (auth()->user()->verification_status !== 'verified') {
            return redirect()->route('manager.dashboard')
                ->with('error', 'Your account must be verified before you can create tournaments. Please wait for verification or contact support if you have questions.');
        }

        $club = Club::where('manager_id', auth()->id())->first();
        
        // Check if club has at least 5 active players
        $activePlayersCount = 0;
        $canCreateTournament = false;
        
        if ($club) {
            $activePlayersCount = \App\Models\ClubPlayer::where('club_id', $club->id)
                ->where('status', 'approved')
                ->count();
            
            $canCreateTournament = $activePlayersCount >= 5;
        }

        return view('manager.tournaments.create', compact('club', 'activePlayersCount', 'canCreateTournament'));
    }

    public function generate(Request $request): View
    {
        // Check if manager is verified
        if (auth()->user()->verification_status !== 'verified') {
            return redirect()->route('manager.dashboard')
                ->with('error', 'Your account must be verified before you can generate tournaments.');
        }

        $club = Club::where('manager_id', auth()->id())->first();
        
        if (!$club) {
            return redirect()->route('manager.tournaments.create')
                ->with('error', 'You must have a club before generating a tournament.');
        }

        // Check if club has at least 5 active players
        $activePlayersCount = \App\Models\ClubPlayer::where('club_id', $club->id)
            ->where('status', 'approved')
            ->count();
        
        if ($activePlayersCount < 5) {
            return redirect()->route('manager.tournaments.create')
                ->with('error', 'Your club must have at least 5 active players before generating a tournament.');
        }

        // Get manager info for prefilling contact details
        $manager = auth()->user();
        
        // Calculate default dates (7 days from now)
        $now = \Carbon\Carbon::now();
        $defaultStartDate = $now->copy()->addDays(7)->format('Y-m-d');
        $defaultEndDate = $now->copy()->addDays(10)->format('Y-m-d');
        $defaultRegistrationDeadline = $now->copy()->addDays(4)->format('Y-m-d');
        $defaultWithdrawalDeadline = $now->copy()->addDays(6)->format('Y-m-d');

        // Default prefilled data - simple and clean
        $prefilledData = [
            'start_date' => $defaultStartDate,
            'end_date' => $defaultEndDate,
            'registration_deadline' => $defaultRegistrationDeadline,
            'withdrawal_deadline' => $defaultWithdrawalDeadline,
            'contact_email' => $manager->email ?? '',
            'contact_phone' => $manager->contact_number ?? '',
            'tournament_fee' => 500,
            'number_of_courts' => 4,
            'bracket_type' => 'single_elimination',
        ];

        $canCreateTournament = $activePlayersCount >= 5;

        return view('manager.tournaments.generate', compact(
            'club', 
            'activePlayersCount', 
            'canCreateTournament', 
            'prefilledData'
        ));
    }

    /**
     * Calculate number of matches for a bracket type
     */
    private function calculateMatchesForBracket(int $slots, string $bracketType): int
    {
        return match($bracketType) {
            'single_elimination' => $slots - 1,
            'round_robin' => ($slots * ($slots - 1)) / 2,
            default => $slots - 1,
        };
    }

    /**
     * Calculate rounds for a bracket type
     */
    private function calculateRoundsForBracket(int $slots, string $bracketType): array
    {
        $rounds = [];
        
        if ($bracketType === 'single_elimination') {
            $numRounds = ceil(log($slots, 2));
            $remainingMatches = $slots;
            
            $roundNames = ['Round 1', 'Round of 16', 'Quarterfinals', 'Semifinals', 'Finals'];
            
            for ($i = 0; $i < $numRounds; $i++) {
                $matchesInRound = ceil($remainingMatches / 2);
                $roundName = $roundNames[$i] ?? "Round " . ($i + 1);
                
                $rounds[] = [
                    'name' => $roundName,
                    'matches' => $matchesInRound,
                ];
                
                $remainingMatches = $matchesInRound;
            }
        } else { // round_robin
            // Round robin: all players play each other once
            $numRounds = $slots - 1;
            $matchesPerRound = floor($slots / 2);
            
            for ($i = 0; $i < $numRounds; $i++) {
                $rounds[] = [
                    'name' => "Round " . ($i + 1),
                    'matches' => $matchesPerRound,
                ];
            }
        }
        
        return $rounds;
    }

    public function store(StoreTournamentRequest $request): RedirectResponse
    {
        // Check if manager is verified
        if (auth()->user()->verification_status !== 'verified') {
            return redirect()->route('manager.dashboard')
                ->with('error', 'Your account must be verified before you can create tournaments. Please wait for verification or contact support if you have questions.');
        }

        DB::beginTransaction();
        
        try {
            $club = Club::where('manager_id', auth()->id())->first();
            
            $tournamentData = $request->except(['banner', 'categories', 'schedules']);
            $tournamentData['club_id'] = $club->id;
            $tournamentData['organizer_id'] = auth()->id();
            $tournamentData['is_dual_meet'] = $request->has('is_dual_meet') && $request->is_dual_meet == '1';
            $tournamentData['status'] = 'upcoming'; // Set default status
            
            // Set location from venue_name if location is not provided
            if (empty($tournamentData['location']) && !empty($tournamentData['venue_name'])) {
                $tournamentData['location'] = $tournamentData['venue_name'];
            }
            
            if ($request->hasFile('banner')) {
                $bannerPath = $request->file('banner')->store('tournament-banners', 'public');
                $tournamentData['banner_path'] = $bannerPath;
            }
            
            $tournament = Tournament::create($tournamentData);
            
            $createdCategories = [];
            foreach ($request->categories as $index => $categoryData) {
                // Parse age requirement to min_age and max_age
                // "Junior (Under 18)" → min_age = null, max_age = 17
                // "Senior (18+)" → min_age = 18, max_age = null
                // "Open (All Ages)" → min_age = null, max_age = null
                $ageRequirement = $categoryData['age_requirement'] ?? 'Open (All Ages)';
                $minAge = null;
                $maxAge = null;
                
                if (str_contains($ageRequirement, 'Junior')) {
                    // Junior: Under 18 → max_age = 17
                    $maxAge = 17;
                    $minAge = null;
                } elseif (str_contains($ageRequirement, 'Senior')) {
                    // Senior: 18+ → min_age = 18
                    $minAge = 18;
                    $maxAge = null;
                } else {
                    // Open: All Ages → both null
                    $minAge = null;
                    $maxAge = null;
                }
                
                // Determine category type for default match duration
                $categoryType = $categoryData['type'] ?? 'MS';
                $scheduleService = app(\App\Services\CategoryScheduleService::class);
                $defaultDuration = $scheduleService->getDefaultMatchDuration($categoryType);
                
                // Format schedule_start_time properly
                $scheduleStartTime = $categoryData['schedule_start_time'] ?? '09:00';
                if (!str_contains($scheduleStartTime, ':')) {
                    $scheduleStartTime = '09:00';
                }
                // Ensure it's in HH:mm format, convert to HH:mm:ss for database
                if (preg_match('/^(\d{2}):(\d{2})$/', $scheduleStartTime)) {
                    $scheduleStartTime .= ':00';
                }
                
                // Set schedule_start_date - use tournament start_date if not specified
                $scheduleStartDate = $categoryData['schedule_start_date'] ?? $tournament->start_date;
                if (is_string($scheduleStartDate)) {
                    try {
                        $scheduleStartDate = \Carbon\Carbon::parse($scheduleStartDate)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $scheduleStartDate = $tournament->start_date;
                    }
                }
                
                $category = TournamentCategory::create([
                    'tournament_id' => $tournament->id,
                    'name' => $categoryData['type'] ?? $categoryData['name'],
                    'max_participants' => $categoryData['slots'] ?? $categoryData['max_participants'] ?? 16,
                    'skill_level' => $categoryData['skill_level_requirements'] ?? $categoryData['skill_level'] ?? null,
                    'min_age' => $minAge,
                    'max_age' => $maxAge,
                    'gender' => $categoryData['gender'] ?? null,
                    'schedule_start_date' => $scheduleStartDate,
                    'schedule_start_time' => $scheduleStartTime,
                    'match_duration_minutes' => $categoryData['match_duration_minutes'] ?? $defaultDuration,
                    'break_between_matches_minutes' => $categoryData['break_between_matches_minutes'] ?? 5,
                ]);
                
                $createdCategories[$index] = $category->id;
            }
            
            // Store match schedules as JSON for later use when generating matches
            // Note: Schedules will be applied when matches are generated
            if ($request->has('schedules') && is_array($request->schedules)) {
                $schedules = [];
                foreach ($request->schedules as $scheduleData) {
                    if (isset($scheduleData['category_id']) && isset($createdCategories[$scheduleData['category_id']])) {
                        $schedules[] = [
                            'category_id' => $createdCategories[$scheduleData['category_id']],
                            'round' => $scheduleData['round'] ?? null,
                            'court' => $scheduleData['court'] ?? null,
                            'date' => $scheduleData['date'] ?? null,
                            'time' => $scheduleData['time'] ?? null,
                        ];
                    }
                }
                // Store in tournament metadata or a separate table if needed
                // For now, we'll apply these when matches are generated
            }
            
            DB::commit();
            
            return redirect()->route('manager.tournaments')
                ->with('success', 'Tournament created successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Tournament creation failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->except(['banner']),
            ]);
            
            return back()->withInput()
                ->with('error', 'Failed to create tournament: ' . $e->getMessage());
        }
    }

    public function show(Tournament $tournament): View
    {
        // All managers can view all tournaments (read-only)
        // Only the owner can manage registrations/withdrawals
        $isOwner = $tournament->club->manager_id === auth()->id();
        
        $tournament->load(['categories', 'registrations.player', 'registrations.category', 'matches']);
        
        return view('manager.tournaments.show', compact('tournament', 'isOwner'));
    }

    public function edit(Tournament $tournament): View
    {
        if ($tournament->club->manager_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this tournament.');
        }
        
        $tournament->load('categories');
        
        return view('manager.tournaments.edit', compact('tournament'));
    }

    public function update(UpdateTournamentRequest $request, Tournament $tournament): RedirectResponse
    {
        try {
            $updateData = $request->except('banner');
            
            if ($request->hasFile('banner')) {
                if ($tournament->banner_path) {
                    Storage::disk('public')->delete($tournament->banner_path);
                }
                
                $bannerPath = $request->file('banner')->store('tournament-banners', 'public');
                $updateData['banner_path'] = $bannerPath;
            }
            
            $tournament->update($updateData);
            
            return redirect()->route('manager.tournaments.show', $tournament->id)
                ->with('success', 'Tournament updated successfully!');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update tournament: ' . $e->getMessage());
        }
    }

    public function destroy(Tournament $tournament): RedirectResponse
    {
        if ($tournament->club->manager_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this tournament.');
        }
        
        if ($tournament->registrations()->count() > 0) {
            return back()->with('error', 'Cannot delete tournament with existing registrations.');
        }
        
        if ($tournament->banner_path) {
            Storage::disk('public')->delete($tournament->banner_path);
        }
        
        $tournament->delete();
        
        return redirect()->route('manager.tournaments')
            ->with('success', 'Tournament deleted successfully!');
    }

    public function publish(Tournament $tournament): RedirectResponse
    {
        if ($tournament->club->manager_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this tournament.');
        }
        
        if ($tournament->categories()->count() === 0) {
            return back()->with('error', 'Cannot publish tournament without categories.');
        }
        
        $tournament->update(['status' => 'published']);
        
        // Notify all registered players that tournament is published
        $registrations = $tournament->registrations()->whereIn('status', ['pending', 'eligible', 'awaiting_payment', 'paid', 'approved'])->get();
        foreach ($registrations as $registration) {
            try {
                $notification = Notification::create([
                    'user_id' => $registration->player_id,
                    'type' => 'tournament_published',
                    'title' => 'Tournament Published',
                    'message' => "{$tournament->name} has been published! Registration is now open.",
                    'data' => ['tournament_id' => $tournament->id],
                    'action_url' => route('player.tournaments.show', $tournament->id),
                ]);

                // Send email notification
                app(\App\Services\EmailService::class)->sendNotificationEmail($notification);
            } catch (\Exception $e) {
                \Log::warning("Failed to send tournament published notification to player {$registration->player_id}: " . $e->getMessage());
            }
        }
        
        return back()->with('success', 'Tournament published successfully!');
    }

    public function generateMatches(Tournament $tournament): RedirectResponse
    {
        if ($tournament->club->manager_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this tournament.');
        }
        
        // Allow 'upcoming' or 'published' status for match generation
        if (!in_array($tournament->status, ['published', 'upcoming'])) {
            return back()->with('error', 'Tournament must be published or upcoming before generating matches.');
        }
        
        // Check if categories have at least 2 approved registrations (minimum for a match)
        foreach ($tournament->categories as $category) {
            $approvedCount = $category->registrations()->where('status', 'approved')->count();
            
            // For doubles/mixed, count teams, not individual registrations
            $isDoublesCategory = str_contains(strtolower($category->name), 'doubles') || 
                                 str_contains(strtolower($category->name), 'mixed');
            
            if ($isDoublesCategory) {
                // Count unique teams (each team has 2 registrations - one for each player)
                $registrations = $category->registrations()
                    ->where('status', 'approved')
                    ->whereNotNull('partner_id')
                    ->get();
                
                $teamKeys = [];
                foreach ($registrations as $reg) {
                    $teamKey = min($reg->player_id, $reg->partner_id) . '_' . max($reg->player_id, $reg->partner_id);
                    if (!in_array($teamKey, $teamKeys)) {
                        $teamKeys[] = $teamKey;
                    }
                }
                $teamCount = count($teamKeys);
                
                if ($teamCount < 2) {
                    return back()->with('error', "Category '{$category->name}' must have at least 2 registered teams before generating matches. Currently has {$teamCount} teams.");
                }
            } else {
                if ($approvedCount < 2) {
                    return back()->with('error', "Category '{$category->name}' must have at least 2 registered participants before generating matches. Currently has {$approvedCount} participants.");
                }
            }
        }
        
        try {
            $bracketType = $tournament->bracket_type ?? 'single_elimination';
            $this->matchGenerationService->generateMatches($tournament, $bracketType);
            
            $tournament->update(['status' => 'ongoing']);
            
            $registrations = $tournament->registrations()->where('status', 'approved')->get();
            foreach ($registrations as $registration) {
                $notification = Notification::create([
                    'user_id' => $registration->player_id,
                    'type' => 'match_scheduled',
                    'title' => 'Tournament Matches Generated',
                    'message' => "Matches have been generated for {$tournament->name}. Check your schedule!",
                    'data' => ['tournament_id' => $tournament->id],
                    'action_url' => route('player.tournaments.show', $tournament->id),
                ]);

                // Send email notification
                app(\App\Services\EmailService::class)->sendNotificationEmail($notification);
            }
            
            return back()->with('success', 'Matches generated successfully!');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate matches: ' . $e->getMessage());
        }
    }

    public function recordResult(Request $request, TournamentMatch $match): RedirectResponse
    {
        if ($match->tournament->club->manager_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this match.');
        }

        $validated = $request->validate([
            'player1_set1_score' => 'required|integer|min:0|max:30',
            'player2_set1_score' => 'required|integer|min:0|max:30',
            'player1_set2_score' => 'nullable|integer|min:0|max:30',
            'player2_set2_score' => 'nullable|integer|min:0|max:30',
            'player1_set3_score' => 'nullable|integer|min:0|max:30',
            'player2_set3_score' => 'nullable|integer|min:0|max:30',
            'winner_id' => 'required|exists:users,id',
        ]);

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
            
            // Find next match using match_number calculation (more reliable than player IDs)
            // This matches the logic in advanceWinner
            $currentRoundMatches = \App\Models\TournamentMatch::where('tournament_id', $match->tournament_id)
                ->where('tournament_category_id', $match->tournament_category_id)
                ->where('round', $match->round)
                ->orderBy('match_number')
                ->get();
            
            $matchPositionInRound = $currentRoundMatches->search(function($m) use ($match) {
                return $m->id === $match->id;
            });
            
            $nextMatchNumber = floor($matchPositionInRound / 2) + 1;
            
            $nextMatch = \App\Models\TournamentMatch::where('tournament_id', $match->tournament_id)
                ->where('tournament_category_id', $match->tournament_category_id)
                ->where('round', $nextRoundName)
                ->orderBy('match_number')
                ->skip($nextMatchNumber - 1)
                ->first();
            
            if ($nextMatch && $nextMatch->status === 'completed') {
                $nextMatchWarning = "Note: The next round match has already been completed. This result will still update brackets and ELO ratings.";
            } elseif ($nextMatch && $nextMatch->status === 'ongoing') {
                $nextMatchWarning = "Note: The next round match has already started. This result will still update brackets and ELO ratings.";
            }
        }

        $validated['match_id'] = $match->id;
        $validated['score_inputted_by'] = 'manager';
        $validated['inputted_by_user_id'] = auth()->id();

        MatchResult::updateOrCreate(
            ['match_id' => $match->id],
            $validated
        );

        // Determine winner partner for doubles/mixed doubles
        $winnerId = $validated['winner_id'];
        $winnerPartnerId = null;
        if ($match->player1_id == $winnerId) {
            $winnerPartnerId = $match->player1_partner_id;
        } elseif ($match->player2_id == $winnerId) {
            $winnerPartnerId = $match->player2_partner_id;
        }

        $match->update([
            'status' => 'completed',
            'winner_id' => $winnerId,
            'winner_partner_id' => $winnerPartnerId,
        ]);

        // Update ELO ratings
        $eloRatingService = app(\App\Services\EloRatingService::class);
        $player1 = $match->player1;
        $player2 = $match->player2;
        
        // Validate players exist before processing
        if (!$player1 || !$player2) {
            return back()->with('error', 'Cannot process result: One or more players not found.');
        }
        
        $player1Won = ($winnerId === $player1->id);

        if (in_array($category->type, ['MD', 'WD', 'XD']) && $match->player1_partner_id && $match->player2_partner_id) {
            $player1Partner = $match->player1Partner;
            $player2Partner = $match->player2Partner;
            
            // Validate partners exist for doubles
            if (!$player1Partner || !$player2Partner) {
                return back()->with('error', 'Cannot process doubles result: One or more partners not found.');
            }
            
            $eloRatingService->calculateDoublesMatchRatings(
                $player1, 
                $player1Partner, 
                $player2, 
                $player2Partner,
                $player1Won,
                $category->type
            );
        } else {
            $eloRatingService->calculateMatchRatings(
                $player1,
                $player2,
                $player1Won,
                $category->type
            );
        }

        // Advance winner to next round (works even if next match already started)
        $this->matchGenerationService->advanceWinner($match);

        // Check if all matches in the tournament are completed
        $tournament = $match->tournament->fresh();
        $allMatches = TournamentMatch::where('tournament_id', $tournament->id)->get();
        $allMatchesCompleted = $allMatches->every(function($m) {
            return $m->status === 'completed';
        });

        // If all matches are completed, update tournament status to completed
        if ($allMatchesCompleted && $tournament->status !== 'completed' && $allMatches->count() > 0) {
            $tournament->update(['status' => 'completed']);
            
            // Notify all registered players that tournament is completed
            $registrations = $tournament->registrations()->where('status', 'approved')->get();
            foreach ($registrations as $registration) {
                try {
                    $notification = Notification::create([
                        'user_id' => $registration->player_id,
                        'type' => 'tournament_completed',
                        'title' => 'Tournament Completed',
                        'message' => "{$tournament->name} has been completed! Check the final results.",
                        'data' => ['tournament_id' => $tournament->id],
                        'action_url' => route('player.tournaments.show', $tournament->id),
                    ]);

                    // Send email notification
                    app(\App\Services\EmailService::class)->sendNotificationEmail($notification);
                } catch (\Exception $e) {
                    \Log::warning("Failed to send tournament completion notification to player {$registration->player_id}: " . $e->getMessage());
                }
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

        // Build success message with warnings if any
        $successMessage = 'Match result recorded, ELO ratings updated, and winner advanced successfully!';
        if ($isLateInput || $nextMatchWarning) {
            $warnings = array_filter([$lateWarning, $nextMatchWarning]);
            if (!empty($warnings)) {
                $successMessage .= ' ' . implode(' ', $warnings);
            }
        }

        return back()->with('success', $successMessage);
    }
}
