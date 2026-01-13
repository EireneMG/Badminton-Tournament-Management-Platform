<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\StoreClubRequest;
use App\Models\Club;
use App\Models\ClubPlayer;
use App\Models\User;
use App\Models\Notification;
use App\Models\ManagerIdVerification;
use App\Services\StatisticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ClubController extends Controller
{
    protected StatisticsService $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    public function index(): View
    {
        $club = Club::where('manager_id', auth()->id())
            ->with(['clubPlayers.player'])
            ->first();
        
        if (!$club) {
            return view('manager.club', [
                'club' => null,
                'joinRequests' => collect([]),
                'approvedPlayers' => collect([]),
                'invitedPlayers' => collect([]),
                'clubStatistics' => null,
            ]);
        }
        
        $joinRequests = \App\Models\ClubPlayer::where('club_id', $club->id)
            ->where('status', 'pending')
            ->where('request_type', 'join_request')
            ->with('player')
            ->get();
        
        $invitedPlayers = \App\Models\ClubPlayer::where('club_id', $club->id)
            ->where('request_type', 'invitation')
            ->where(function($query) {
                $query->whereIn('status', ['invited', 'rejected'])
                    ->orWhere(function($q) {
                        $q->where('status', 'approved')
                          ->where(function($subQ) {
                              $subQ->whereNull('skill_level')
                                   ->orWhereNull('provisional_elo');
                          });
                    });
            })
            ->with('player')
            ->get()
            ->filter(function($clubPlayer) {
                return $clubPlayer->player !== null;
            })
            ->sortBy(function($clubPlayer) {
                return strtolower($clubPlayer->player->first_name . ' ' . $clubPlayer->player->last_name);
            })
            ->values();
        
        $approvedPlayers = \App\Models\ClubPlayer::where('club_id', $club->id)
            ->where('status', 'approved')
            ->whereNotNull('skill_level')
            ->whereNotNull('provisional_elo')
            ->with('player')
            ->get()
            ->filter(function($clubPlayer) {
                return $clubPlayer->player !== null;
            })
            ->sortBy(function($clubPlayer) {
                return strtolower($clubPlayer->player->first_name . ' ' . $clubPlayer->player->last_name);
            })
            ->values();
        
        try {
            $clubStatistics = $this->statisticsService->getClubStatistics($club);
        } catch (\Exception $e) {
            \Log::error("Error fetching club statistics for club {$club->id}: " . $e->getMessage());
            $clubStatistics = [
                'total_members' => 0,
                'active_members' => 0,
                'tournaments_hosted' => 0,
                'tournaments_by_status' => ['published' => 0, 'upcoming' => 0, 'ongoing' => 0, 'completed' => 0],
                'total_matches' => 0,
                'completed_matches' => 0,
                'average_elo' => 0,
            ];
        }
        
        return view('manager.club', compact('club', 'joinRequests', 'invitedPlayers', 'approvedPlayers', 'clubStatistics'));
    }

    /**
     * Display all clubs in the system (for managers to browse).
     */
    public function allClubs(): View
    {
        $allClubs = Club::withCount('approvedPlayers')
            ->where('active', true)
            ->with('manager')
            ->orderBy('name')
            ->get();

        // Get current manager's club (if exists)
        $currentManagerClub = Club::where('manager_id', auth()->id())->first();

        return view('manager.clubs', compact('allClubs', 'currentManagerClub'));
    }

    /**
     * Display another club's details (read-only view for managers).
     */
    public function showClub(Club $club): View
    {
        // Get current manager's club
        $currentManagerClub = Club::where('manager_id', auth()->id())->first();

        // If viewing own club, redirect to My Club
        if ($currentManagerClub && $club->id === $currentManagerClub->id) {
            return redirect()->route('manager.club');
        }

        // Load club with manager and tournaments relationships
        $club->load(['manager', 'tournaments']);

        // Get approved players using ClubPlayer model (to access skill_level, provisional_elo, etc.)
        $approvedPlayers = ClubPlayer::where('club_id', $club->id)
            ->where('status', 'approved')
            ->whereNotNull('skill_level')
            ->whereNotNull('provisional_elo')
            ->with('player')
            ->get()
            ->filter(function($clubPlayer) {
                return $clubPlayer->player !== null;
            })
            ->sortBy(function($clubPlayer) {
                return strtolower($clubPlayer->player->first_name . ' ' . $clubPlayer->player->last_name);
            })
            ->values();

        return view('manager.club-view', compact('club', 'currentManagerClub', 'approvedPlayers'));
    }

    /**
     * Get player biodata for manager view (JSON response for modal).
     */
    public function getPlayerBiodata(User $player): \Illuminate\Http\JsonResponse
    {
        // Only managers can access this
        if (!auth()->user()->isManager()) {
            abort(403, 'Only club managers can view player biodata.');
        }

        // Calculate age (ensure integer)
        $age = null;
        if ($player->birth_year && $player->birth_month && $player->birth_day) {
            $birthDate = \Carbon\Carbon::createFromDate($player->birth_year, $player->birth_month, $player->birth_day);
            $age = (int)$birthDate->diffInYears(\Carbon\Carbon::now());
        }

        // Format badminton history labels
        $historyLabels = [
            'tournament' => 'Tournament Experience',
            'school_event' => 'School Event',
            'community_event' => 'Community Event',
            'club_member' => 'Club Member',
            'recreational' => 'Recreational',
            'coached' => 'Coached',
            'varsity' => 'Varsity Player',
        ];

        $badmintonHistory = [];
        if ($player->badminton_history && is_array($player->badminton_history)) {
            foreach ($player->badminton_history as $history) {
                $badmintonHistory[] = $historyLabels[$history] ?? ucfirst($history);
            }
        }

        // Format education status
        $educationStatusLabels = [
            'junior_high' => 'Junior High School Student',
            'senior_high' => 'Senior High School Student',
            'college_student' => 'College Student',
            'college_graduate' => 'College Graduate',
            'post_graduate' => 'Post-Graduate (Master\'s/Doctorate)',
            'not_applicable' => 'N/A (Did not attend school)',
            'student' => 'Currently a Student', // Legacy support
            'graduated' => 'Graduated', // Legacy support
        ];
        $educationStatus = $educationStatusLabels[$player->school_status] ?? ucfirst(str_replace('_', ' ', $player->school_status ?? 'N/A'));

        // Format years of experience
        $yearsLabels = [
            'less_than_1' => 'Less than 1 year',
            '1_2' => '1–2 years',
            '3_5' => '3–5 years',
            '6_10' => '6–10 years',
            'more_than_10' => 'More than 10 years',
        ];
        $yearsOfExperience = $yearsLabels[$player->years_of_experience] ?? ($player->years_of_experience ?? 'N/A');

        // Format experience level
        $experienceLevelLabels = [
            'beginner' => 'Beginner',
            'lower_intermediate' => 'Lower Intermediate',
            'intermediate' => 'Intermediate',
            'upper_intermediate' => 'Upper Intermediate',
            'advanced' => 'Advanced',
        ];
        $experienceLevel = $experienceLevelLabels[$player->experience_level] ?? ($player->experience_level ?? 'N/A');

        // Format competitive background
        $competitiveLabels = [
            'school_competitions' => 'School competitions',
            'local_tournaments' => 'Local tournaments',
            'regional_tournaments' => 'Regional tournaments',
            'national_tournaments' => 'National tournaments',
            'none' => 'None',
        ];
        $competitiveBackground = $competitiveLabels[$player->competitive_background] ?? ($player->competitive_background ?? 'N/A');

        // Format ID type
        $idTypeLabels = [
            'drivers_license' => 'Driver\'s License',
            'student_id' => 'Student ID',
            'passport' => 'Passport',
            'national_id' => 'National ID',
            'prc_id' => 'PRC ID',
            'postal_id' => 'Postal ID',
            'senior_citizen_id' => 'Senior Citizen ID',
            'others' => 'Others',
        ];
        $idType = $idTypeLabels[$player->id_type] ?? ($player->id_type ?? 'N/A');

        return response()->json([
            'player' => [
                'id' => $player->id,
                'first_name' => $player->first_name,
                'middle_name' => $player->middle_name,
                'last_name' => $player->last_name,
                'email' => $player->email,
                'contact_number' => $player->contact_number,
                'gender' => ucfirst($player->gender ?? 'N/A'),
                'birth_date' => $player->birth_month && $player->birth_day && $player->birth_year
                    ? \Carbon\Carbon::createFromDate($player->birth_year, $player->birth_month, $player->birth_day)->format('F d, Y')
                    : 'N/A',
                'age' => $age ?? 'N/A',
                'location' => trim(($player->city ?? '') . ', ' . ($player->province ?? '') . ', ' . ($player->region ?? ''), ', '),
                'height' => $player->height ? $player->height . ' cm' : 'N/A',
                'weight' => $player->weight ? $player->weight . ' kg' : 'N/A',
                'school_status' => $educationStatus,
                'school_name' => $player->school_name ?? 'N/A',
                'years_of_experience' => $yearsOfExperience,
                'experience_level' => $experienceLevel,
                'competitive_background' => $competitiveBackground,
                'badminton_history' => $badmintonHistory,
                'id_type' => $idType,
                'profile_photo' => $player->profile_photo ? Storage::url($player->profile_photo) : null,
                'player_id_document' => $player->player_id_document ? Storage::url($player->player_id_document) : null,
                'biodata_completed' => $player->biodata_completed,
            ]
        ]);
    }

    /**
     * Display the club creation form.
     */
    public function create(): View
    {
        return view('manager.create-club');
    }

    /**
     * Handle the club creation.
     */
    public function store(StoreClubRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Check if manager already has a club
        if ($user->managedClub) {
            return back()->with('error', 'You have already created a club.');
        }

        // Handle manager ID verification upload (required at club creation) and auto-verify
        $idFilePath = $request->file('id_file')->store('manager-ids', 'public');

        ManagerIdVerification::updateOrCreate(
            ['manager_id' => $user->id],
            [
                'id_type' => $request->id_type,
                'id_file_path' => $idFilePath,
                'status' => 'verified',
                'submitted_at' => now(),
            ]
        );
        $user->update(['verification_status' => 'verified']);

        $clubData = [
            'manager_id' => $user->id,
            'name' => $request->name,
            'description' => $request->description,
            'province' => $request->province,
            'city' => $request->city,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'active' => true,
        ];

        // Handle logo upload if provided
        if ($request->hasFile('logo')) {
            $clubData['logo'] = $request->file('logo')->store('club-logos', 'public');
        }

        Club::create($clubData);

        return redirect()->route('manager.dashboard')
            ->with('success', 'Club created successfully! Welcome to BadminTour.');
    }

    /**
     * Display the club edit form.
     */
    public function edit(): View
    {
        $club = Club::where('manager_id', auth()->id())->first();

        if (!$club) {
            return redirect()->route('manager.club')
                ->with('error', 'You need to create a club first.');
        }

        return view('manager.edit-club', compact('club'));
    }

    /**
     * Update the club information.
     */
    public function update(Request $request): RedirectResponse
    {
        $club = Club::where('manager_id', auth()->id())->first();

        if (!$club) {
            return redirect()->route('manager.club')
                ->with('error', 'You need to create a club first.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'province' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($request->hasFile('logo')) {
            if ($club->logo) {
                Storage::disk('public')->delete($club->logo);
            }
            $validated['logo'] = $request->file('logo')->store('club-logos', 'public');
        }

        // Use database transaction to ensure data integrity
        \Illuminate\Support\Facades\DB::transaction(function() use ($club, $validated) {
            $club->update($validated);
        });

        return redirect()->route('manager.club')
            ->with('success', 'Club information updated successfully!');
    }

    /**
     * Invite a player to join the club.
     */
    public function invitePlayer(Request $request): RedirectResponse
    {
        $club = Club::where('manager_id', auth()->id())->first();

        if (!$club) {
            return redirect()->route('manager.club')
                ->with('error', 'You need to create a club first.');
        }

        // Accept either player_id or player_email
        $validated = $request->validate([
            'player_id' => 'nullable|exists:users,id',
            'player_email' => 'nullable|email|exists:users,email',
            'message' => 'nullable|string|max:500',
        ]);

        // Get player by ID or email
        if ($request->has('player_id') && $request->player_id) {
            $player = User::findOrFail($validated['player_id']);
        } elseif ($request->has('player_email') && $request->player_email) {
            $player = User::where('email', $validated['player_email'])->firstOrFail();
        } else {
            return back()->with('error', 'Please provide either a player ID or email address.');
        }

        if ($player->role !== 'player') {
            return back()->with('error', 'Selected user is not a player.');
        }

        // Check if player is already in this manager's club (any status)
        $existingMembership = ClubPlayer::where('club_id', $club->id)
            ->where('player_id', $player->id)
            ->whereIn('status', ['pending', 'approved', 'invited', 'rejected'])
            ->first();

        if ($existingMembership) {
            if ($existingMembership->status === 'approved') {
                return back()->with('error', 'This player is already a member of your club.');
            } elseif ($existingMembership->status === 'invited') {
                return back()->with('error', 'This player has already been invited to your club.');
            } elseif ($existingMembership->status === 'pending') {
                return back()->with('error', 'This player has a pending request to join your club.');
            } else {
                return back()->with('error', 'This player has a previous relationship with your club.');
            }
        }

        // Check if player is already a member of ANY other club
        $alreadyInAnotherClub = ClubPlayer::where('player_id', $player->id)
            ->where('club_id', '!=', $club->id)
            ->where('status', 'approved')
            ->exists();

        if ($alreadyInAnotherClub) {
            return back()->with('error', 'This player is already a member of another club. Only players without club affiliation can be invited.');
        }

        // Create invitation WITHOUT provisional skill level
        ClubPlayer::create([
            'club_id' => $club->id,
            'player_id' => $player->id,
            'status' => 'invited',
            'request_type' => 'invitation',
            // No provisional_elo, skill_level, or is_provisional set during invite
        ]);

        $notification = Notification::create([
            'user_id' => $player->id,
            'type' => 'club_invitation',
            'title' => 'Club Invitation',
            'message' => "You have been invited to join {$club->name}." . ($validated['message'] ? " Message: {$validated['message']}" : ''),
            'data' => ['club_id' => $club->id],
            'action_url' => route('player.dashboard'),
        ]);

        // Send email notification
        app(\App\Services\EmailService::class)->sendNotificationEmail($notification);

        return back()->with('success', "Invitation sent to {$player->first_name} {$player->last_name}!");
    }

    /**
     * Remove a player from the club.
     */
    public function removePlayer(ClubPlayer $clubPlayer): RedirectResponse
    {
        $club = Club::where('manager_id', auth()->id())->first();

        if (!$club || $clubPlayer->club_id !== $club->id) {
            return back()->with('error', 'Unauthorized action.');
        }

        $playerName = $clubPlayer->player->first_name . ' ' . $clubPlayer->player->last_name;
        
        // If it's an invitation, notify the player that the invitation was cancelled
        if ($clubPlayer->status === 'invited' && $clubPlayer->request_type === 'invitation') {
            Notification::create([
                'user_id' => $clubPlayer->player_id,
                'type' => 'club_invitation_cancelled',
                'title' => 'Club Invitation Cancelled',
                'message' => "Your invitation to join {$club->name} has been cancelled by the club manager.",
                'data' => ['club_id' => $club->id],
            ]);
            
            $clubPlayer->delete();
            return back()->with('success', "Invitation to {$playerName} has been cancelled.");
        }
        
        // For approved members, send removal notification
        $notification = Notification::create([
            'user_id' => $clubPlayer->player_id,
            'type' => 'removed_from_club',
            'title' => 'Removed from Club',
            'message' => "You have been removed from {$club->name}.",
            'data' => ['club_id' => $club->id],
            'action_url' => route('player.dashboard'),
        ]);

        // Send email notification
        app(\App\Services\EmailService::class)->sendNotificationEmail($notification);

        $clubPlayer->delete();

        return back()->with('success', "{$playerName} has been removed from the club.");
    }

    /**
     * Update a player's skill level.
     */
    public function updateSkillLevel(Request $request, ClubPlayer $clubPlayer): RedirectResponse
    {
        $club = Club::where('manager_id', auth()->id())->first();

        if (!$club || $clubPlayer->club_id !== $club->id) {
            return back()->with('error', 'Unauthorized action.');
        }

        // Prevent changing provisional skill levels
        if ($clubPlayer->is_provisional) {
            return back()->with('error', 'Cannot update skill level. This is a provisional skill level derived from the player\'s ranking/ELO rating and cannot be changed by club managers.');
        }

        $validated = $request->validate([
            'skill_level' => 'nullable|string|in:A,B,C,D',
            'provisional_elo' => 'nullable|integer|min:0|max:3000',
        ]);

        $clubPlayer->update($validated);

        return back()->with('success', 'Player skill level updated successfully!');
    }

    /**
     * Assign provisional skill level to an accepted invitation.
     */
    public function assignProvisionalSkillLevel(ClubPlayer $clubPlayer, Request $request): RedirectResponse
    {
        $club = Club::where('manager_id', auth()->id())->first();

        if (!$club || $clubPlayer->club_id !== $club->id) {
            return back()->with('error', 'Unauthorized action.');
        }

        // Only allow assigning to invitations (invited or approved status) that don't have provisional skill level yet
        if ($clubPlayer->request_type !== 'invitation') {
            return back()->with('error', 'Can only assign provisional skill level to invitations.');
        }

        if (!in_array($clubPlayer->status, ['invited', 'approved'])) {
            return back()->with('error', 'Can only assign provisional skill level to pending or accepted invitations.');
        }

        // Check if already has provisional skill level
        if ($clubPlayer->skill_level && $clubPlayer->provisional_elo) {
            return back()->with('error', 'Provisional skill level has already been assigned to this player.');
        }

        $validated = $request->validate([
            'provisional_skill_level' => 'required|string|in:A,B,C,D',
        ]);

        // Convert provisional skill level to ELO rating
        $provisionalElo = \App\Helpers\SkillLevelHelper::convertSkillLevelToElo($validated['provisional_skill_level']);

        // Create official ELO rating from provisional
        $this->createEloFromProvisional($clubPlayer->player, $provisionalElo);

        // Update club player record - set status to 'approved' and assign provisional skill level
        // This moves the player from Invited Players to Club Members
        $clubPlayer->update([
            'status' => 'approved', // Now officially a club member
            'provisional_elo' => $provisionalElo,
            'skill_level' => $validated['provisional_skill_level'],
            'is_provisional' => true, // Mark as provisional
        ]);

        // Notify player
        Notification::create([
            'user_id' => $clubPlayer->player_id,
            'type' => 'club_invitation_provisional_assigned',
            'title' => 'Provisional Skill Level Assigned',
            'message' => "Your provisional skill level (Level {$validated['provisional_skill_level']}) has been assigned for {$club->name}. This has been converted to an initial ELO rating of {$provisionalElo}.",
            'data' => ['club_id' => $club->id],
            'action_url' => route('clubs.show', $club->id),
        ]);

        return back()->with('success', "Provisional skill level (Level {$validated['provisional_skill_level']}) assigned successfully!");
    }

    /**
     * Create official ELO rating from provisional skill level
     * Creates ELO records for all gender-appropriate categories
     */
    protected function createEloFromProvisional(\App\Models\User $player, int $eloRating): void
    {
        // Determine categories based on player gender
        // Male players: MS, MD, XD
        // Female players: WS, WD, XD
        $isMale = $player->gender === 'Male';
        $categories = $isMale 
            ? ['MS', 'MD', 'XD']  // Male categories
            : ['WS', 'WD', 'XD']; // Female categories
        
        // Create or update ELO ratings for all gender-appropriate categories
        foreach ($categories as $category) {
            \App\Models\EloRating::updateOrCreate(
                [
                    'player_id' => $player->id,
                    'category' => $category,
                ],
                [
                    'current_rating' => $eloRating,
                    'peak_rating' => $eloRating,
                    'matches_played' => 0,
                ]
            );
            
            // Create ranking history entry for each category
            \App\Models\RankingHistory::create([
                'player_id' => $player->id,
                'category' => $category,
                'rating' => $eloRating,
                'previous_rating' => null,
                'change' => 0,
                'recorded_at' => now(),
            ]);
        }
    }

}
