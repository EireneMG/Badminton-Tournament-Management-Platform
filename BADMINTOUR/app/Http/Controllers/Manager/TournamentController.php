<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTournamentRequest;
use App\Models\Tournament;
use App\Models\TournamentCategory;
use App\Models\Club;
use App\Models\Notification;
use App\Models\TournamentMatch;
use App\Models\MatchResult;
use App\Services\MatchGenerationService;
use App\Services\TournamentStatusService;
use App\Enums\TournamentStatus;
use App\Enums\MatchStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TournamentController extends Controller
{
    protected $matchGenerationService;
    protected $tournamentStatusService;

    public function __construct(MatchGenerationService $matchGenerationService, TournamentStatusService $tournamentStatusService)
    {
        $this->matchGenerationService = $matchGenerationService;
        $this->tournamentStatusService = $tournamentStatusService;
    }
    
    /**
     * Check if the current manager can manage matches in a tournament
     * For regular tournaments: Only tournament owner (club manager)
     * For dual meet tournaments: Tournament owner OR any participating club manager
     * 
     * @param Tournament $tournament
     * @return bool
     */
    protected function canManageTournamentMatches(Tournament $tournament): bool
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

    public function index(): View
    {
        // Update tournament statuses before displaying
        $this->tournamentStatusService->updateTournamentStatuses();
        
        $club = Club::where('manager_id', auth()->id())->first();

        $sort = request('sort', 'start_date');
        $dir = strtolower(request('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sorter = function ($collection) use ($sort, $dir) {
            $mult = $dir === 'desc' ? -1 : 1;
            return $collection->sort(function ($a, $b) use ($sort, $mult) {
                $map = [
                    'name' => [$a->name ?? '', $b->name ?? ''],
                    'start_date' => [$a->start_date ?? '', $b->start_date ?? ''],
                    'registration_deadline' => [$a->registration_deadline ?? '', $b->registration_deadline ?? ''],
                    'venue' => [$a->venue_name ?? $a->venue ?? '', $b->venue_name ?? $b->venue ?? ''],
                    'organizer' => [$a->club->name ?? '', $b->club->name ?? ''],
                    'categories' => [
                        implode(', ', optional($a->categories)->pluck('name')->toArray() ?? []),
                        implode(', ', optional($b->categories)->pluck('name')->toArray() ?? [])
                    ],
                ];
                $key = $map[$sort] ?? $map['start_date'];
                if ($key[0] == $key[1]) {
                    return $mult * (($a->id ?? 0) <=> ($b->id ?? 0));
                }
                return $mult * (($key[0] <=> $key[1]));
            })->values();
        };
        
        // Get ALL tournaments for Upcoming, Ongoing, Completed tabs
        $allTournaments = Tournament::with('categories', 'club')
            ->get();
        $allTournaments = $sorter($allTournaments);
        
        // Get manager's tournaments for "Your Tournaments" tab
        $myTournaments = collect([]);
        if ($club) {
            $myTournaments = Tournament::where('club_id', $club->id)
                ->with('categories')
                ->get();
        }
        $myTournaments = $sorter($myTournaments);

        return view('manager.tournaments', compact('allTournaments', 'myTournaments', 'club', 'sort', 'dir'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $user = auth()->user();
        if ($user && str_contains(strtolower($user->email ?? ''), 'manager.test')) {
            $club = Club::firstWhere('manager_id', $user->id);
            $activePlayersCount = $club?->approvedPlayers()->count() ?? 10;
            $canCreateTournament = true;
            $otherClubs = collect();
            return view('manager.tournaments.create', compact('club', 'activePlayersCount', 'canCreateTournament', 'otherClubs'));
        }

        $club = Club::where('manager_id', auth()->id())->first();
        if (!$club) {
            return redirect()->to('/manager/tournaments')
                ->with('error', 'You must have a club before creating a tournament.');
        }

        // Check if club has at least 5 active players
        $activePlayersCount = \App\Models\ClubPlayer::where('club_id', $club->id)
            ->where('status', 'approved')
            ->count();
        
        $canCreateTournament = $activePlayersCount >= 5;
        if (!$canCreateTournament) {
            return redirect()->to('/manager/tournaments')
                ->with('error', 'You must have at least 5 approved players in your club to create a tournament.');
        }

        // Require verified ID after club and player-count checks
        $verification = auth()->user()->managerIdVerification;
        if (!$verification || $verification->status !== 'verified') {
            return redirect()->route('manager.verify-id')
                ->with('error', 'Please upload your ID to proceed. Once uploaded, verification is automatic.');
        }

        // Get all clubs except the current manager's club for dual meet selection
        $otherClubs = Club::where('active', true)
            ->where('id', '!=', $club->id ?? 0)
            ->with('manager')
            ->orderBy('name')
            ->get();

        return view('manager.tournaments.create', compact('club', 'activePlayersCount', 'canCreateTournament', 'otherClubs'));
    }

    public function generate(Request $request): View|RedirectResponse
    {
        try {
            $user = auth()->user();
            if ($user && str_contains(strtolower($user->email ?? ''), 'manager.test')) {
                // Ensure verified status and club for test managers
                if ($user->verification_status !== 'verified') {
                    $user->verification_status = 'verified';
                    $user->save();
                }
                $club = Club::firstOrCreate(
                    ['manager_id' => $user->id],
                    [
                        'name' => 'Test Club ' . ($user->id ?? 'Demo'),
                        'description' => 'Auto-created club for test manager (generate).',
                        'contact_email' => $user->email,
                        'contact_phone' => '+63 900 000 0000',
                        'province' => 'NCR',
                        'city' => 'Quezon City',
                        'active' => true,
                    ]
                );
                $activePlayersCount = $club?->approvedPlayers()->count() ?? 10;
                $otherClubs = collect();
                $canCreateTournament = true;

                // Prefill defaults
                $now = \Carbon\Carbon::now();
                $prefilledData = [
                    'start_date' => $now->copy()->addDays(7)->format('Y-m-d'),
                    'end_date' => $now->copy()->addDays(10)->format('Y-m-d'),
                    'registration_deadline' => $now->copy()->addDays(4)->format('Y-m-d'),
                    'withdrawal_deadline' => $now->copy()->addDays(6)->format('Y-m-d'),
                    'contact_email' => $user->email ?? '',
                    'contact_phone' => $user->contact_number ?? '',
                    'tournament_fee' => 500,
                    'number_of_courts' => 4,
                    'bracket_type' => 'single_elimination',
                ];

                return view('manager.tournaments.generate', compact('club', 'activePlayersCount', 'otherClubs', 'prefilledData', 'canCreateTournament'));
            }

            $club = Club::where('manager_id', auth()->id())->first();
            
            if (!$club) {
                return redirect()->to('/manager/tournaments')
                    ->with('error', 'You must have a club before generating a tournament.');
            }

            // Check if club has at least 5 active players
            $activePlayersCount = \App\Models\ClubPlayer::where('club_id', $club->id)
                ->where('status', 'approved')
                ->count();
            
            if ($activePlayersCount < 5) {
                return redirect()->to('/manager/tournaments')
                    ->with('error', 'You must have at least 5 approved players in your club to generate a tournament.');
            }

            // Require verified ID after club and player-count checks
            // Use optional() to safely access the relationship
            $verification = optional(auth()->user())->managerIdVerification;
            if (!$verification || $verification->status !== 'verified') {
                return redirect()->route('manager.verify-id')
                    ->with('error', 'Please upload your ID to proceed. Once uploaded, verification is automatic.');
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

            // Get all clubs except the current manager's club for dual meet selection
            // Use safe eager loading to avoid errors if manager relationship is missing
            try {
                $otherClubs = Club::where('active', true)
                    ->where('id', '!=', $club->id)
                    ->whereNotNull('manager_id') // Only get clubs that have a manager_id
                    ->with('manager')
                    ->orderBy('name')
                    ->get();
            } catch (\Exception $e) {
                Log::error('Error loading other clubs in generate: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
                $otherClubs = collect(); // Empty collection if there's an error
            }

            return view('manager.tournaments.generate', compact(
                'club', 
                'activePlayersCount',
                'otherClubs', 
                'canCreateTournament', 
                'prefilledData'
            ));
        } catch (\Exception $e) {
            Log::error('Error in TournamentController::generate: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return redirect()->to('/manager/tournaments')
                ->with('error', 'An error occurred while loading the tournament generation page. Please try again.');
        }
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
        $user = auth()->user();
        $email = strtolower($user->email ?? '');
        $isTestManager = str_contains($email, 'manager.test');

        // Allow seeded test managers to bypass verification/club prerequisites
        if ($isTestManager) {
            if ($user->verification_status !== 'verified') {
                $user->verification_status = 'verified';
                $user->save();
            }
            $club = Club::firstOrCreate(
                ['manager_id' => $user->id],
                [
                    'name' => 'Test Club ' . ($user->id ?? 'Demo'),
                    'description' => 'Auto-created club for test manager.',
                    'contact_email' => $user->email,
                    'contact_phone' => '+63 900 000 0000',
                    'province' => 'NCR',
                    'city' => 'Quezon City',
                    'active' => true,
                ]
            );
        } else {
            // Check if manager is verified
            if ($user->verification_status !== 'verified') {
                return redirect()->route('manager.dashboard')
                    ->with('error', 'Your account must be verified before you can create tournaments. Please wait for verification or contact support if you have questions.');
            }
            $club = Club::where('manager_id', $user->id)->first();
            if (!$club) {
                return redirect()->route('manager.dashboard')
                    ->with('error', 'Please create a club before creating a tournament.');
            }
        }

        DB::beginTransaction();
        
        try {
            if (!$club) {
                throw new \RuntimeException('Club not found for manager.');
            }
            
            $tournamentData = $request->except(['banner', 'categories', 'schedules']);
            $tournamentData['club_id'] = $club->id;
            $tournamentData['organizer_id'] = auth()->id();
            $tournamentData['is_dual_meet'] = $request->has('is_dual_meet') && $request->is_dual_meet == '1';
            
            // All tournaments are created as published
            $tournamentData['status'] = 'published';
            
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
            if (empty($request->categories) || !is_array($request->categories)) {
                throw new \RuntimeException('At least one category is required.');
            }
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
                
                $slots = (int) ($categoryData['slots'] ?? $categoryData['max_participants'] ?? 16);
                if (!in_array($slots, [16, 32], true)) {
                    $slots = 16;
                }

                $category = TournamentCategory::create([
                    'tournament_id' => $tournament->id,
                    'name' => $categoryData['type'] ?? $categoryData['name'],
                    'max_participants' => $slots,
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
            
            // Send notifications to any existing registrations (usually none at creation time)
            $registrations = $tournament->registrations()->whereIn('status', ['pending', 'eligible', 'approved'])->get();
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
            
            // Send notification to tournament host about their responsibilities
            try {
                $managerUser = auth()->user();
                if ($managerUser) {
                    $roleMessage = $tournament->is_dual_meet 
                        ? "You are the host manager for the dual meet tournament '{$tournament->name}'. As the host, you will be responsible for: Approving/rejecting player registrations, Accepting player withdrawals, Inputting all match scores, Marking walkovers, and Rescheduling matches. Participating club managers will not have access to these functions."
                        : "You are the manager for the tournament '{$tournament->name}'. As the tournament owner, you will manage all tournament aspects including registrations, match input, and match management.";
                    
                    Notification::create([
                        'user_id' => $managerUser->id,
                        'type' => 'tournament_created',
                        'title' => 'Tournament Created - Host Responsibilities',
                        'message' => $roleMessage,
                        'data' => [
                            'tournament_id' => $tournament->id,
                            'is_dual_meet' => $tournament->is_dual_meet,
                        ],
                        'action_url' => route('manager.tournaments.show', $tournament->id),
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to send host manager notification: " . $e->getMessage());
            }
            
            // If dual meet, send invitation notification to the invited club manager
            if ($tournament->is_dual_meet && $request->has('invited_club_id')) {
                $invitedClubId = $request->input('invited_club_id');
                if (!empty($invitedClubId)) {
                    try {
                        $invitedClub = Club::find($invitedClubId);
                        if ($invitedClub && $invitedClub->manager) {
                            Notification::create([
                                'user_id' => $invitedClub->manager_id,
                                'type' => 'dual_meet_invitation',
                                'title' => 'Dual Meet Tournament Invitation',
                                'message' => "Your club '{$invitedClub->name}' has been invited to participate in the dual meet tournament '{$tournament->name}' hosted by {$club->name}. Players from your club can now register. Note: The tournament host will handle all match management including score input, walkovers, and rescheduling.",
                                'data' => [
                                    'tournament_id' => $tournament->id,
                                    'host_club_id' => $club->id,
                                    'host_club_name' => $club->name,
                                ],
                                'action_url' => route('manager.tournaments.show', $tournament->id),
                            ]);

                            // Send email notification
                            app(\App\Services\EmailService::class)->sendNotificationEmail(
                                Notification::latest()->first()
                            );
                        }
                    } catch (\Exception $e) {
                        \Log::warning("Failed to send invitation to club {$invitedClubId}: " . $e->getMessage());
                    }
                }
            }
            
            $successMessage = $tournament->is_dual_meet && $request->has('invited_club_id') && !empty($request->input('invited_club_id'))
                ? 'Tournament created and published successfully! The invited club has been notified.'
                : 'Tournament created and published successfully!';
            
            return redirect()->route('manager.tournaments')
                ->with('success', $successMessage);
                
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
        // Update tournament status if needed (upcoming → ongoing → completed)
        // This ensures status is always current when viewing
        $this->tournamentStatusService->updateTournamentStatuses($tournament);
        $tournament->refresh();
        
        // All managers can view all tournaments (read-only)
        // Only the owner can manage registrations/withdrawals
        $isOwner = $tournament->club && $tournament->club->manager_id === auth()->id();
        
        // Check if current manager is an INVITED participant in a dual meet (not the host)
        $isDualMeetInvited = false;
        $hostClubName = $tournament->club->name ?? 'Unknown Club';
        
        if ($tournament->is_dual_meet && !$isOwner) {
            $managerClub = Club::where('manager_id', auth()->id())->first();
            if ($managerClub) {
                // Check if manager's club has players registered in this tournament
                $hasParticipatingPlayers = \App\Models\TournamentRegistration::where('tournament_id', $tournament->id)
                    ->whereHas('player', function($query) use ($managerClub) {
                        $query->whereHas('clubMemberships', function($q) use ($managerClub) {
                            $q->where('club_id', $managerClub->id)->where('status', 'approved');
                        });
                    })
                    ->exists();
                
                $isDualMeetInvited = $hasParticipatingPlayers;
            }
        }

        // Identify seeded test tournaments to keep them isolated from real users
        $seededNames = [
            'Completed Open 1',
            'Completed Open 2',
            'Ongoing Test Tournament',
            'Upcoming Test Tournament',
        ];
        $isSeededTournament = in_array($tournament->name, $seededNames, true);
        $seededEmailPattern = 'player.test%';
        
        $tournament->load([
            'categories',
            'registrations.player',
            'registrations.partner',
            'registrations.category',
            'matches.result',
            'matches.player1',
            'matches.player2',
            'matches.player1Partner',
            'matches.player2Partner',
            'matches.winner',
            'matches.winnerPartner',
        ]);

        $standingsByCategory = [];
        if ($tournament->bracket_type === 'round_robin') {
            $standingsService = app(\App\Services\RoundRobinStandingsService::class);
            foreach ($tournament->categories as $category) {
                $standingsByCategory[$category->id] = $standingsService->calculateStandings($category);
            }
        }
        
        // Get all approved registrations grouped by category for player registrations display (exclude withdrawn)
        // Reload registrations from database to ensure we have the latest status
        $registrationsByCategory = [];
        foreach ($tournament->categories as $category) {
            // Query fresh from database to avoid cached relationships
            $registrationsQuery = \App\Models\TournamentRegistration::where('category_id', $category->id)
                ->where('tournament_id', $tournament->id)
                ->where('status', 'approved')
                ->with(['player', 'partner']);

            // For seeded tournaments, ensure only seeded sample players are shown
            if ($isSeededTournament) {
                $registrationsQuery->whereHas('player', function ($q) use ($seededEmailPattern) {
                    $q->where('email', 'like', $seededEmailPattern);
                });
                $registrationsQuery->where(function ($q) use ($seededEmailPattern) {
                    $q->whereNull('partner_id')
                        ->orWhereHas('partner', function ($qp) use ($seededEmailPattern) {
                            $qp->where('email', 'like', $seededEmailPattern);
                        });
                });
            }

            $registrationsByCategory[$category->id] = $registrationsQuery->get();
        }
        
        // Build matches grouped by category and round for read-only history display
        $matchesByCategory = [];
        // Participant counts per category to normalize rounds
        $participantCounts = [];
        foreach ($registrationsByCategory as $catId => $regs) {
            $participantCounts[$catId] = $regs->count();
        }
        // Fallback participant counts from matches (in case no registrations counted)
        $categoryPlayerCounts = [];
        foreach ($tournament->matches as $m) {
            $catId = $m->tournament_category_id;
            if (!isset($categoryPlayerCounts[$catId])) {
                $categoryPlayerCounts[$catId] = collect();
            }
            $categoryPlayerCounts[$catId] = $categoryPlayerCounts[$catId]
                ->merge([$m->player1_id, $m->player2_id, $m->player1_partner_id, $m->player2_partner_id])
                ->filter();
        }

        $roundSorter = function ($round) use ($tournament) {
            if ($tournament->bracket_type === 'round_robin') {
                $r = strtolower(trim((string)$round));
                if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                    return (int)$m[1];
                }
                return 9999;
            }
            
            $r = strtolower(trim((string)$round));
            if (preg_match('/round of (\d+)/i', $r, $m)) return 10 + (int)$m[1];
            if ($r === 'quarterfinals' || str_contains($r, 'quarter')) return 30;
            if ($r === 'semifinals' || str_contains($r, 'semi')) return 40;
            if ($r === 'finals' || str_contains($r, 'final')) return 50;
            if (preg_match('/round\s*(\d+)/i', $r, $m)) return 100 + (int)$m[1];
            if (is_numeric($round)) return (int)$round;
            return 999;
        };

        $roundLabelFn = function ($roundName, int $slots, int $participants, $match = null) use ($tournament) {
            if ($tournament->bracket_type === 'round_robin') {
                $r = strtolower(trim((string) $roundName));
                if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                    return 'Round ' . (int)$m[1];
                }
                return ucfirst($roundName);
            }
            
            $basis = max(2, $participants ?: $slots);
            
            if ($match && is_numeric($roundName)) {
                $maxRounds = (int)ceil(log($basis, 2));
                return \App\Helpers\TournamentRoundHelper::getRoundName(
                    'single_elimination',
                    (int)$roundName,
                    $maxRounds
                );
            }
            
            $r = strtolower(trim((string) $roundName));
            if (preg_match('/round of (\d+)/i', $r, $m)) return 'Round of ' . (int)$m[1];
            if (str_contains($r, 'quarter')) return 'Quarterfinals';
            if (str_contains($r, 'semi')) return 'Semifinals';
            if (str_contains($r, 'final')) return 'Finals';

            $idx = null;
            if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                $idx = (int)$m[1];
            } elseif (is_numeric($roundName)) {
                $idx = (int)$roundName;
            }

            if ($idx !== null) {
                $progression = \App\Helpers\TournamentRoundHelper::getRoundProgressionForBracketSize($basis);
                if (!empty($progression) && isset($progression[$idx - 1])) {
                    return $progression[$idx - 1];
                }
            }

            return ucfirst($roundName);
        };

        $matches = $tournament->matches->unique('id');
        // Backfill winner fields from result if missing (for seeded completed data)
        foreach ($matches as $match) {
            if ($match->status === MatchStatus::COMPLETED->value && (!$match->winner_id || !$match->winner)) {
                $res = $match->result;
                if ($res) {
                    $winnerId = $res->winner_id;
                    if (!$winnerId) {
                        $team1Total = ($res->player1_set1_score ?? 0) + ($res->player1_set2_score ?? 0) + ($res->player1_set3_score ?? 0);
                        $team2Total = ($res->player2_set1_score ?? 0) + ($res->player2_set2_score ?? 0) + ($res->player2_set3_score ?? 0);
                        $winnerId = $team1Total >= $team2Total ? $match->player1_id : $match->player2_id;
                    }
                    $winnerPartnerId = null;
                    if ($winnerId === $match->player1_id) {
                        $winnerPartnerId = $match->player1_partner_id;
                    } elseif ($winnerId === $match->player2_id) {
                        $winnerPartnerId = $match->player2_partner_id;
                    }
                    $match->winner_id = $winnerId;
                    $match->winner_partner_id = $winnerPartnerId;
                }
            }
        }
        foreach ($matches as $match) {
            if ($match->result && $match->status !== MatchStatus::COMPLETED->value) {
                $match->status = MatchStatus::COMPLETED->value;
            }
        }
        if ($tournament->status === TournamentStatus::COMPLETED->value) {
            $matches = $matches->where('status', MatchStatus::COMPLETED->value);
        }
        if ($isSeededTournament) {
            $matches = $matches->filter(function ($match) {
                $p1Ok = $match->player1 ? str_contains($match->player1->email, 'player.test') : true;
                $p2Ok = $match->player2 ? str_contains($match->player2->email, 'player.test') : true;
                $p1pOk = $match->player1Partner ? str_contains($match->player1Partner->email, 'player.test') : true;
                $p2pOk = $match->player2Partner ? str_contains($match->player2Partner->email, 'player.test') : true;
                $wOk = $match->winner ? str_contains($match->winner->email, 'player.test') : true;
                $wpOk = $match->winnerPartner ? str_contains($match->winnerPartner->email, 'player.test') : true;
                return $p1Ok && $p2Ok && $p1pOk && $p2pOk && $wOk && $wpOk;
            });
        }

        foreach ($matches as $match) {
            $categoryId = $match->tournament_category_id;
            $categoryModel = $tournament->categories->firstWhere('id', $categoryId);
            $categorySlots = $categoryModel?->max_participants ?? ($categoryModel?->slots ?? 0);
            $participants = $participantCounts[$categoryId] ?? 0;
            if ($participants === 0 && isset($categoryPlayerCounts[$categoryId])) {
                $uniquePlayers = $categoryPlayerCounts[$categoryId]->unique()->count();
                $isDoubles = false;
                if ($categoryModel) {
                    $name = strtolower($categoryModel->name ?? '');
                    $matchType = strtolower($categoryModel->match_type ?? '');
                    $type = strtolower($categoryModel->type ?? '');
                    $isDoubles = str_contains($name, 'doubles') || str_contains($name, 'mixed')
                        || str_contains($matchType, 'double') || str_contains($matchType, 'mixed')
                        || in_array($type, ['md', 'wd', 'xd', 'doubles', 'mixed'], true);
                }
                $participants = $isDoubles ? (int) ceil($uniquePlayers / 2) : $uniquePlayers;
            }
            $roundLabel = $roundLabelFn($match->round ?? 'Round 1', (int) $categorySlots, (int) $participants, $match);
            if (!isset($matchesByCategory[$categoryId])) {
                $matchesByCategory[$categoryId] = [];
            }
            if (!isset($matchesByCategory[$categoryId][$roundLabel])) {
                $matchesByCategory[$categoryId][$roundLabel] = [];
            }
            $matchesByCategory[$categoryId][$roundLabel][] = $match;
        }

        // Sort matches inside each round and sort rounds themselves for stable display
        foreach ($matchesByCategory as $catId => $rounds) {
            // sort rounds by numeric weight
            uksort($rounds, function ($a, $b) use ($roundSorter) {
                return $roundSorter($a) <=> $roundSorter($b);
            });
            // sort matches by match_number to avoid duplicates/unstable order
            foreach ($rounds as $rName => $roundMatches) {
                $matchesByCategory[$catId][$rName] = collect($roundMatches)
                    ->unique('id')
                    ->sortBy('match_number')
                    ->values()
                    ->all();
            }
            $matchesByCategory[$catId] = $rounds;
        }

        // ensure categories relation remains unique
        $tournament->setRelation('categories', $tournament->categories->unique('id')->values());

        // Get pending withdrawal request IDs to check which registrations have pending withdrawals
        $pendingWithdrawalRequestIds = \App\Models\WithdrawalRequest::whereIn('tournament_registration_id', $tournament->registrations->pluck('id'))
            ->where('status', 'pending')
            ->pluck('tournament_registration_id')
            ->toArray();
        
        // Check if tournament can be deleted (within 24 hours of creation)
        $canDelete = false;
        if ($isOwner) {
            $createdAt = $tournament->created_at;
            $hoursSinceCreation = $createdAt ? now()->diffInHours($createdAt) : 999;
            $canDelete = $hoursSinceCreation <= 24;
        }
        
        return view('manager.tournaments.show', compact(
            'tournament',
            'isOwner',
            'isDualMeetInvited',
            'hostClubName',
            'registrationsByCategory',
            'pendingWithdrawalRequestIds',
            'matchesByCategory',
            'standingsByCategory',
            'canDelete'
        ));
    }


    public function destroy(Tournament $tournament): RedirectResponse
    {
        if (!$tournament->club || $tournament->club->manager_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this tournament.');
        }
        
        // Allow deletion only within 24 hours of creation
        $createdAt = $tournament->created_at;
        $hoursSinceCreation = $createdAt ? now()->diffInHours($createdAt) : 999;
        
        if ($hoursSinceCreation > 24) {
            return back()->with('error', 'Tournaments can only be deleted within 24 hours of creation.');
        }
        
        if ($tournament->banner_path) {
            Storage::disk('public')->delete($tournament->banner_path);
        }
        
        $tournament->delete();
        
        return redirect()->route('manager.tournaments')
            ->with('success', 'Tournament deleted successfully!');
    }


    public function generateMatches(Tournament $tournament): RedirectResponse
    {
        $user = auth()->user();
        $email = strtolower($user->email ?? '');
        $isTestManager = str_contains($email, 'manager.test');

        if (!$isTestManager && (!$tournament->club || $tournament->club->manager_id !== auth()->id())) {
            abort(403, 'Unauthorized access to this tournament.');
        }
        
        // Allow 'published' or 'upcoming' status for match generation
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
        // STRICT MODE: Only the tournament HOST can input match results
        // Invited club managers in dual meet tournaments can only view
        if (!$match->tournament || !$match->tournament->club || $match->tournament->club->manager_id !== auth()->id()) {
            abort(403, 'Unauthorized: Only the tournament host manager can input match results. This tournament is managed by the host club.');
        }

        // Wrap everything in a database transaction to ensure atomicity
        return DB::transaction(function () use ($request, $match) {
            $validated = $request->validate([
                'player1_set1_score' => 'required|integer|min:0|max:30',
                'player2_set1_score' => 'required|integer|min:0|max:30',
                'player1_set2_score' => 'required|integer|min:0|max:30',
                'player2_set2_score' => 'required|integer|min:0|max:30',
                'player1_set3_score' => 'nullable|integer|min:0|max:30',
                'player2_set3_score' => 'nullable|integer|min:0|max:30',
                'winner_id' => 'nullable|exists:users,id',
            ]);
            
            // Validate badminton scoring rules and Set 3 requirement
            $matchScoreService = app(\App\Services\MatchScoreService::class);
            $scoreValidation = $matchScoreService->validateSetScores($validated);
            
            $errors = [];
            
            // Add badminton rule validation errors
            if (!empty($scoreValidation['errors'])) {
                if (isset($scoreValidation['errors']['set1'])) {
                    $errors['player1_set1_score'] = 'Set 1: ' . $scoreValidation['errors']['set1'];
                    $errors['player2_set1_score'] = 'Set 1: ' . $scoreValidation['errors']['set1'];
                }
                if (isset($scoreValidation['errors']['set2'])) {
                    $errors['player1_set2_score'] = 'Set 2: ' . $scoreValidation['errors']['set2'];
                    $errors['player2_set2_score'] = 'Set 2: ' . $scoreValidation['errors']['set2'];
                }
                if (isset($scoreValidation['errors']['set3'])) {
                    $errors['player1_set3_score'] = 'Set 3: ' . $scoreValidation['errors']['set3'];
                    $errors['player2_set3_score'] = 'Set 3: ' . $scoreValidation['errors']['set3'];
                }
            }
            
            // Validate Set 3 requirement: if sets are tied 1-1, Set 3 is required
            if ($scoreValidation['set3_required'] && !$scoreValidation['set3_provided']) {
                $errors['player1_set3_score'] = 'Set 3 is required because the match is tied 1-1 after Set 1 and Set 2.';
                $errors['player2_set3_score'] = 'Set 3 is required because the match is tied 1-1 after Set 1 and Set 2.';
            }
            
            if (!empty($errors)) {
                return back()->withErrors($errors)->withInput();
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
                
                if ($nextMatch && $nextMatch->status === MatchStatus::COMPLETED->value) {
                    $nextMatchWarning = "Note: The next round match has already been completed. This result will still update brackets and ELO ratings.";
                } elseif ($nextMatch && $nextMatch->status === MatchStatus::ONGOING->value) {
                    $nextMatchWarning = "Note: The next round match has already started. This result will still update brackets and ELO ratings.";
                }
            }

            $matchScoreService = app(\App\Services\MatchScoreService::class);
            
            $tempResult = new \App\Models\MatchResult();
            $tempResult->player1_set1_score = $validated['player1_set1_score'];
            $tempResult->player2_set1_score = $validated['player2_set1_score'];
            $tempResult->player1_set2_score = $validated['player1_set2_score'];
            $tempResult->player2_set2_score = $validated['player2_set2_score'];
            $tempResult->player1_set3_score = $validated['player1_set3_score'] ?? null;
            $tempResult->player2_set3_score = $validated['player2_set3_score'] ?? null;
            
            $finalScore = $matchScoreService->calculateFinalScore($tempResult);
            
            $winnerId = null;
            if ($finalScore['player1_sets'] > $finalScore['player2_sets']) {
                $winnerId = $match->player1_id;
            } elseif ($finalScore['player2_sets'] > $finalScore['player1_sets']) {
                $winnerId = $match->player2_id;
            }
            
            if (!$winnerId) {
                return back()->withErrors(['player1_set1_score' => 'Unable to determine winner from set scores. Please check that all set scores are valid.'])->withInput();
            }

            $validated['match_id'] = $match->id;
            $validated['score_inputted_by'] = 'manager';
            $validated['inputted_by_user_id'] = auth()->id();
            $validated['winner_id'] = $winnerId;

            // Save match result to database
            MatchResult::updateOrCreate(
                ['match_id' => $match->id],
                $validated
            );

            // Determine winner partner for doubles/mixed doubles
            $winnerPartnerId = null;
            if ($match->player1_id == $winnerId) {
                $winnerPartnerId = $match->player1_partner_id;
            } elseif ($match->player2_id == $winnerId) {
                $winnerPartnerId = $match->player2_partner_id;
            }

            // Update match status and winner
            $match->update([
                'status' => 'completed',
                'winner_id' => $winnerId,
                'winner_partner_id' => $winnerPartnerId,
            ]);

            // Refresh match to get updated result
            $match->refresh();
            $result = $match->result;
            
            if (!$result) {
                throw new \Exception('Match result was not saved properly. Cannot update ELO ratings.');
            }

            // Update ELO ratings using MatchScoreService (unified system)
            $matchScoreService = app(\App\Services\MatchScoreService::class);
            $eloUpdateResult = $matchScoreService->processMatchResultAndUpdateElo($match, $result);
            
            $successMessage = 'Match result saved successfully.';
            if ($eloUpdateResult['success'] && !empty($eloUpdateResult['elo_updates'])) {
                $eloDetails = [];
                foreach ($eloUpdateResult['elo_updates'] as $playerKey => $update) {
                    $change = $update['change'];
                    $sign = $change >= 0 ? '+' : '';
                    $eloDetails[] = "{$update['name']}: {$update['old_elo']} → {$update['new_elo']} ({$sign}{$change})";
                }
                $successMessage .= ' ELO ratings updated: ' . implode(', ', $eloDetails) . '.';
            } else {
                \Log::warning('ELO update failed for match', [
                    'match_id' => $match->id,
                    'error' => $eloUpdateResult['message'] ?? 'Unknown error',
                ]);
                // Continue with match processing even if ELO update fails (log but don't block)
            }

            // Advance winner to next round (works even if next match already started)
            $this->matchGenerationService->advanceWinner($match);

            // Check if tournament should be marked as completed
            $tournament = $match->tournament->fresh();
            $wasCompleted = $tournament->status === TournamentStatus::COMPLETED->value;
            
            $this->tournamentStatusService->checkAndUpdateCompletionStatus($tournament);
            $tournament->refresh();
            
            if (!$wasCompleted && $tournament->status === TournamentStatus::COMPLETED->value) {
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
            // Note: This is already handled in MatchScoreService, but we keep it here for redundancy
            $playersToNotify = [];
            
            $player1 = $match->player1;
            $player2 = $match->player2;
            
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

            // Add warnings if any
            if ($isLateInput || $nextMatchWarning) {
                $warnings = array_filter([$lateWarning, $nextMatchWarning]);
                if (!empty($warnings)) {
                    $successMessage .= ' ' . implode(' ', $warnings);
                }
            }

            return back()->with('success', $successMessage);
        });
    }

    /**
     * Lightweight helper to reuse normalized round ordering.
     */
    protected function roundSorter(): callable
    {
        return function ($round) {
            $r = strtolower(trim((string)$round));
            if (preg_match('/round of (\d+)/i', $r, $m)) return 10 + (int) $m[1];
            if ($r === 'quarterfinals' || str_contains($r, 'quarter')) return 30;
            if ($r === 'semifinals' || str_contains($r, 'semi')) return 40;
            if ($r === 'finals' || str_contains($r, 'final')) return 50;
            if (preg_match('/round\s*(\d+)/i', $r, $m)) return 100 + (int) $m[1];
            if (is_numeric($round)) return (int) $round;
            return 999;
        };
    }

    /**
     * Normalize round labels to standard names based on slots/participants.
     */
    protected function roundLabelFn(): callable
    {
        return function ($roundName, int $slots, int $participants, $match = null) {
            if ($match && $match->tournament && $match->tournament->bracket_type === 'round_robin') {
                $r = strtolower(trim((string) $roundName));
                if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                    return 'Round ' . (int)$m[1];
                }
                return ucfirst($roundName);
            }
            
            $basis = max(2, $participants ?: $slots);
            
            if ($match && $match->tournament && is_numeric($roundName)) {
                $maxRounds = (int)ceil(log($basis, 2));
                return \App\Helpers\TournamentRoundHelper::getRoundName(
                    'single_elimination',
                    (int)$roundName,
                    $maxRounds
                );
            }
            
            $r = strtolower(trim((string) $roundName));
            if (preg_match('/round of (\d+)/i', $r, $m)) return 'Round of ' . (int) $m[1];
            if (str_contains($r, 'quarter')) return 'Quarterfinals';
            if (str_contains($r, 'semi')) return 'Semifinals';
            if (str_contains($r, 'final')) return 'Finals';

            $idx = null;
            if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                $idx = (int) $m[1];
            } elseif (is_numeric($roundName)) {
                $idx = (int) $roundName;
            }

            if ($idx !== null) {
                $progression = \App\Helpers\TournamentRoundHelper::getRoundProgressionForBracketSize($basis);
                if (!empty($progression) && isset($progression[$idx - 1])) {
                    return $progression[$idx - 1];
                }
            }

            return ucfirst($roundName);
        };
    }

    /**
     * Build matches grouped by category and normalized round for reuse (manager matches & show).
     */
    public function buildMatchesByCategory(Tournament $tournament): array
    {
        $seededNames = [
            'Completed Open 1',
            'Completed Open 2',
            'Ongoing Test Tournament',
            'Upcoming Test Tournament',
        ];
        $isSeededTournament = in_array($tournament->name, $seededNames, true);
        $seededEmailPattern = 'player.test%';

        $tournament->loadMissing([
            'categories',
            'registrations.player',
            'registrations.partner',
            'matches.result',
            'matches.player1',
            'matches.player2',
            'matches.player1Partner',
            'matches.player2Partner',
            'matches.winner',
            'matches.winnerPartner',
        ]);

        // Registrations per category (approved only) for participant counts
        $registrationsByCategory = [];
        foreach ($tournament->categories as $category) {
            $registrationsQuery = \App\Models\TournamentRegistration::where('category_id', $category->id)
                ->where('tournament_id', $tournament->id)
                ->where('status', 'approved')
                ->with(['player', 'partner']);

            if ($isSeededTournament) {
                $registrationsQuery->whereHas('player', function ($q) use ($seededEmailPattern) {
                    $q->where('email', 'like', $seededEmailPattern);
                });
                $registrationsQuery->where(function ($q) use ($seededEmailPattern) {
                    $q->whereNull('partner_id')
                        ->orWhereHas('partner', function ($qp) use ($seededEmailPattern) {
                            $qp->where('email', 'like', $seededEmailPattern);
                        });
                });
            }

            $registrationsByCategory[$category->id] = $registrationsQuery->get();
        }

        $participantCounts = [];
        foreach ($registrationsByCategory as $catId => $regs) {
            $participantCounts[$catId] = $regs->count();
        }

        $categoryPlayerCounts = [];
        foreach ($tournament->matches as $m) {
            $catId = $m->tournament_category_id;
            if (!isset($categoryPlayerCounts[$catId])) {
                $categoryPlayerCounts[$catId] = collect();
            }
            $categoryPlayerCounts[$catId] = $categoryPlayerCounts[$catId]
                ->merge([$m->player1_id, $m->player2_id, $m->player1_partner_id, $m->player2_partner_id])
                ->filter();
        }

        $roundSorter = function ($round) use ($tournament) {
            if ($tournament->bracket_type === 'round_robin') {
                $r = strtolower(trim((string)$round));
                if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                    return (int)$m[1];
                }
                return 9999;
            }
            $r = strtolower(trim((string)$round));
            if (preg_match('/round of (\d+)/i', $r, $m)) return 10 + (int)$m[1];
            if ($r === 'quarterfinals' || str_contains($r, 'quarter')) return 30;
            if ($r === 'semifinals' || str_contains($r, 'semi')) return 40;
            if ($r === 'finals' || str_contains($r, 'final')) return 50;
            if (preg_match('/round\s*(\d+)/i', $r, $m)) return 100 + (int)$m[1];
            if (is_numeric($round)) return (int)$round;
            return 999;
        };
        
        $roundLabelFn = function ($roundName, int $slots, int $participants, $match = null) use ($tournament) {
            if ($tournament->bracket_type === 'round_robin') {
                $r = strtolower(trim((string) $roundName));
                if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                    return 'Round ' . (int)$m[1];
                }
                return ucfirst($roundName);
            }
            
            $basis = max(2, $participants ?: $slots);
            
            if ($match && is_numeric($roundName)) {
                $maxRounds = (int)ceil(log($basis, 2));
                return \App\Helpers\TournamentRoundHelper::getRoundName(
                    'single_elimination',
                    (int)$roundName,
                    $maxRounds
                );
            }
            
            $r = strtolower(trim((string) $roundName));
            if (preg_match('/round of (\d+)/i', $r, $m)) return 'Round of ' . (int)$m[1];
            if (str_contains($r, 'quarter')) return 'Quarterfinals';
            if (str_contains($r, 'semi')) return 'Semifinals';
            if (str_contains($r, 'final')) return 'Finals';

            $idx = null;
            if (preg_match('/round\s*(\d+)/i', $r, $m)) {
                $idx = (int)$m[1];
            } elseif (is_numeric($roundName)) {
                $idx = (int)$roundName;
            }

            if ($idx !== null) {
                $progression = \App\Helpers\TournamentRoundHelper::getRoundProgressionForBracketSize($basis);
                if (!empty($progression) && isset($progression[$idx - 1])) {
                    return $progression[$idx - 1];
                }
            }

            return ucfirst($roundName);
        };

        $matches = $tournament->matches->unique('id');
        foreach ($matches as $match) {
            if ($match->status === MatchStatus::COMPLETED->value && (!$match->winner_id || !$match->winner)) {
                $res = $match->result;
                if ($res) {
                    $winnerId = $res->winner_id;
                    if (!$winnerId) {
                        $team1Total = ($res->player1_set1_score ?? 0) + ($res->player1_set2_score ?? 0) + ($res->player1_set3_score ?? 0);
                        $team2Total = ($res->player2_set1_score ?? 0) + ($res->player2_set2_score ?? 0) + ($res->player2_set3_score ?? 0);
                        $winnerId = $team1Total >= $team2Total ? $match->player1_id : $match->player2_id;
                    }
                    $winnerPartnerId = null;
                    if ($winnerId === $match->player1_id) {
                        $winnerPartnerId = $match->player1_partner_id;
                    } elseif ($winnerId === $match->player2_id) {
                        $winnerPartnerId = $match->player2_partner_id;
                    }
                    $match->winner_id = $winnerId;
                    $match->winner_partner_id = $winnerPartnerId;
                }
            }
        }

        foreach ($matches as $match) {
            if ($match->result && $match->status !== MatchStatus::COMPLETED->value) {
                $match->status = MatchStatus::COMPLETED->value;
            }
        }

        if ($tournament->status === TournamentStatus::COMPLETED->value) {
            $matches = $matches->where('status', MatchStatus::COMPLETED->value);
        }

        if ($isSeededTournament) {
            $matches = $matches->filter(function ($match) {
                $p1Ok = $match->player1 ? str_contains($match->player1->email, 'player.test') : true;
                $p2Ok = $match->player2 ? str_contains($match->player2->email, 'player.test') : true;
                $p1pOk = $match->player1Partner ? str_contains($match->player1Partner->email, 'player.test') : true;
                $p2pOk = $match->player2Partner ? str_contains($match->player2Partner->email, 'player.test') : true;
                $wOk = $match->winner ? str_contains($match->winner->email, 'player.test') : true;
                $wpOk = $match->winnerPartner ? str_contains($match->winnerPartner->email, 'player.test') : true;
                return $p1Ok && $p2Ok && $p1pOk && $p2pOk && $wOk && $wpOk;
            });
        }

        $matchesByCategory = [];
        foreach ($matches as $match) {
            $categoryId = $match->tournament_category_id;
            $categoryModel = $tournament->categories->firstWhere('id', $categoryId);
            $categorySlots = $categoryModel?->max_participants ?? ($categoryModel?->slots ?? 0);
            $participants = $participantCounts[$categoryId] ?? 0;
            if ($participants === 0 && isset($categoryPlayerCounts[$categoryId])) {
                $uniquePlayers = $categoryPlayerCounts[$categoryId]->unique()->count();
                $isDoubles = false;
                if ($categoryModel) {
                    $name = strtolower($categoryModel->name ?? '');
                    $matchType = strtolower($categoryModel->match_type ?? '');
                    $type = strtolower($categoryModel->type ?? '');
                    $isDoubles = str_contains($name, 'doubles') || str_contains($name, 'mixed')
                        || str_contains($matchType, 'double') || str_contains($matchType, 'mixed')
                        || in_array($type, ['md', 'wd', 'xd', 'doubles', 'mixed'], true);
                }
                $participants = $isDoubles ? (int) ceil($uniquePlayers / 2) : $uniquePlayers;
            }
            $roundLabel = $roundLabelFn($match->round ?? 'Round 1', (int) $categorySlots, (int) $participants, $match);
            if (!isset($matchesByCategory[$categoryId])) {
                $matchesByCategory[$categoryId] = [];
            }
            if (!isset($matchesByCategory[$categoryId][$roundLabel])) {
                $matchesByCategory[$categoryId][$roundLabel] = [];
            }
            $matchesByCategory[$categoryId][$roundLabel][] = $match;
        }

        foreach ($matchesByCategory as $catId => $rounds) {
            uksort($rounds, function ($a, $b) use ($roundSorter) {
                return $roundSorter($a) <=> $roundSorter($b);
            });
            foreach ($rounds as $rName => $roundMatches) {
                $matchesByCategory[$catId][$rName] = collect($roundMatches)
                    ->unique('id')
                    ->sortBy('match_number')
                    ->values()
                    ->all();
            }
            $matchesByCategory[$catId] = $rounds;
        }

        $tournament->setRelation('categories', $tournament->categories->unique('id')->values());

        return $matchesByCategory;
    }

    public function reschedule(Request $request, Tournament $tournament): RedirectResponse
    {
        if (!$tournament->club || $tournament->club->manager_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this tournament.');
        }
        
        if (!in_array($tournament->status, [TournamentStatus::PUBLISHED->value, TournamentStatus::UPCOMING->value])) {
            return back()->with('error', 'Only published or upcoming tournaments can be rescheduled.');
        }
        
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);
        
        $oldStartDate = $tournament->start_date;
        $oldEndDate = $tournament->end_date;
        
        $tournament->update([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
        
        if ($request->start_date !== $oldStartDate) {
            $this->recalculateTournamentDays($tournament, $oldStartDate, $request->start_date);
        }
        
        $oldStartDateFormatted = Carbon::parse($oldStartDate)->format('M d, Y');
        $newStartDateFormatted = Carbon::parse($request->start_date)->format('M d, Y');
        $oldEndDateFormatted = Carbon::parse($oldEndDate)->format('M d, Y');
        $newEndDateFormatted = Carbon::parse($request->end_date)->format('M d, Y');
        
        $registrations = $tournament->registrations()
            ->whereIn('status', ['pending', 'eligible', 'approved'])
            ->with('player')
            ->get();
        
        foreach ($registrations as $registration) {
            if (!$registration->player) {
                continue;
            }
            
            try {
                $notification = Notification::create([
                    'user_id' => $registration->player_id,
                    'type' => 'tournament_rescheduled',
                    'title' => 'Tournament Rescheduled',
                    'message' => "{$tournament->name} has been rescheduled. New dates: {$newStartDateFormatted} - {$newEndDateFormatted}.",
                    'data' => [
                        'tournament_id' => $tournament->id,
                        'old_start_date' => $oldStartDateFormatted,
                        'new_start_date' => $newStartDateFormatted,
                        'old_end_date' => $oldEndDateFormatted,
                        'new_end_date' => $newEndDateFormatted,
                    ],
                    'action_url' => route('player.tournaments.show', $tournament->id),
                ]);
                
                app(\App\Services\EmailService::class)->sendNotificationEmail($notification);
            } catch (\Exception $e) {
                Log::warning("Failed to send tournament rescheduled notification to player {$registration->player_id}: " . $e->getMessage());
            }
        }
        
        return redirect()->route('manager.tournaments.show', $tournament->id)
            ->with('success', 'Tournament rescheduled successfully! All match dates have been updated and players have been notified.');
    }

    protected function recalculateTournamentDays(Tournament $tournament, string $oldStartDate, string $newStartDate): void
    {
        try {
            $oldDate = Carbon::parse($oldStartDate);
            $newDate = Carbon::parse($newStartDate);
            $dayOffset = $oldDate->diffInDays($newDate);
            
            if ($dayOffset === 0) {
                return;
            }
            
            TournamentMatch::where('tournament_id', $tournament->id)
                ->whereNotNull('tournament_day')
                ->get()
                ->each(function($match) use ($dayOffset) {
                    $newDay = $match->tournament_day + $dayOffset;
                    if ($newDay < 1) {
                        $newDay = 1;
                    }
                    $match->update(['tournament_day' => $newDay]);
                });
            
            Log::info("Recalculated tournament_day for all matches in tournament {$tournament->id} (offset: {$dayOffset} days)");
        } catch (\Exception $e) {
            Log::error("Failed to recalculate tournament days for tournament {$tournament->id}: " . $e->getMessage());
        }
    }
}
