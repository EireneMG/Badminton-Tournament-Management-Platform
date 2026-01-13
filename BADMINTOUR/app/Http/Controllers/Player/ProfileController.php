<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TournamentMatch;
use App\Models\TournamentRegistration;
use App\Models\RankingHistory;
use App\Models\MatchResult;
use App\Models\EloRating;
use App\Models\TournamentCategory;
use App\Services\StatisticsService;
use App\Enums\TournamentStatus;
use App\Enums\MatchStatus;
use App\Enums\CategoryType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $player = auth()->user();
        return view('players.edit', compact('player'));
    }

    public function update(Request $request): RedirectResponse
    {
        $player = auth()->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'birth_month' => 'nullable|integer|min:1|max:12',
            'birth_day' => 'nullable|integer|min:1|max:31',
            'birth_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'height' => 'nullable|numeric|min:50|max:250',
            'weight' => 'nullable|numeric|min:20|max:200',
            'region' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'school_status' => 'nullable|in:junior_high,senior_high,college_student,college_graduate,post_graduate,not_applicable,student,graduated',
            'school_name' => 'nullable|string|max:255',
            'badminton_history' => 'nullable|array',
            'badminton_history.*' => 'string',
            'years_of_experience' => 'nullable|in:less_than_1,1_2,3_5,6_10,more_than_10',
            'experience_level' => 'nullable|in:beginner,lower_intermediate,intermediate,upper_intermediate,advanced',
            'competitive_background' => 'nullable|in:school_competitions,local_tournaments,regional_tournaments,national_tournaments,none',
            'id_type' => 'nullable|in:drivers_license,student_id,passport,national_id,birth_certificate,prc_id,postal_id,senior_citizen_id,others',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // Require an ID upload if not already present
            'player_id_document' => [$player->player_id_document ? 'nullable' : 'required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'],
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($player->profile_photo) {
                Storage::disk('public')->delete($player->profile_photo);
            }
            $validated['profile_photo'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        if ($request->hasFile('player_id_document')) {
            if ($player->player_id_document) {
                Storage::disk('public')->delete($player->player_id_document);
            }
            $validated['player_id_document'] = $request->file('player_id_document')->store('player-ids', 'public');
        }

        // Gender is immutable after registration/verification
        $validated['gender'] = $player->gender;

        // Preserve existing location when fields are disabled/empty; save new selection when provided
        // Handle region: use submitted value if provided, otherwise preserve existing
        $validated['region'] = $request->filled('region') ? $request->input('region') : ($player->region ?? null);
        
        // Handle province: special case for NCR (hidden field submits 'N/A'), otherwise use submitted or preserve existing
        if ($request->has('province')) {
            $validated['province'] = $request->input('province');
        } else {
            $validated['province'] = $player->province ?? null;
        }
        
        // Handle city: use submitted value (including from hidden field when disabled), otherwise preserve existing
        if ($request->has('city')) {
            $validated['city'] = $request->input('city');
        } else {
            $validated['city'] = $player->city ?? null;
        }

        // Check if biodata was incomplete before update
        $wasIncomplete = !$player->biodata_completed;
        
        $validated['biodata_completed'] = true;

        // Use database transaction to ensure data integrity
        DB::transaction(function() use ($player, $validated) {
            $player->update($validated);
        });

        // If this was the first time completing biodata, redirect to dashboard
        if ($wasIncomplete) {
            return redirect()->route('player.dashboard')->with('success', 'Profile completed successfully! You can now access all features of the system.');
        }

        return redirect()->route('profile.index')->with('success', 'Profile updated successfully!');
    }

    public function show(): View
    {
        $player = auth()->user();
        
        $clubMembership = $player->approvedClubMembership;
        $club = $clubMembership ? $clubMembership->club : null;
        
        $age = null;
        try {
            if ($player->birth_year && $player->birth_month && $player->birth_day) {
                $birthDate = Carbon::createFromDate($player->birth_year, $player->birth_month, $player->birth_day);
                $age = (int)$birthDate->diffInYears(Carbon::now());
            }
        } catch (InvalidFormatException|\Exception $e) {
            \Log::error("Invalid birth date for player {$player->id}: " . $e->getMessage());
            $age = null;
        }
        
        try {
            $statisticsService = app(StatisticsService::class);
            $stats = $statisticsService->getPlayerStatistics($player);
        } catch (\Exception $e) {
            \Log::error("Error fetching player statistics for player {$player->id}: " . $e->getMessage());
            $stats = [
                'total_matches' => 0,
                'wins' => 0,
                'losses' => 0,
                'win_rate' => 0,
                'category_stats' => [],
                'recent_win_rate' => 0,
                'recent_wins' => 0,
                'recent_losses' => 0,
                'average_points' => 0,
                'tournament_stats' => ['tournaments_joined' => 0, 'tournaments_completed' => 0, 'best_finish' => 'N/A'],
                'elo_ratings' => collect([]),
                'ranking_history' => collect([]),
            ];
        }
        
        $matches = TournamentMatch::where(function($query) use ($player) {
            $query->where('player1_id', $player->id)
                  ->orWhere('player2_id', $player->id)
                  ->orWhere('player1_partner_id', $player->id)
                  ->orWhere('player2_partner_id', $player->id);
        })->with(['result', 'tournament', 'category', 'player1', 'player2', 'player1Partner', 'player2Partner'])->get();
        
        $recentMatches = $matches->filter(function($match) {
            return $match->status === MatchStatus::COMPLETED->value && $match->result;
        })->sortByDesc('updated_at')->take(10);
        
        $gender = $player->gender ?? 'Male';
        $categoriesToLoad = $gender === 'Female' 
            ? [CategoryType::WOMENS_SINGLES->value, CategoryType::WOMENS_DOUBLES->value, CategoryType::MIXED_DOUBLES->value]
            : [CategoryType::MENS_SINGLES->value, CategoryType::MENS_DOUBLES->value, CategoryType::MIXED_DOUBLES->value];
        
        $eloRatings = EloRating::where('player_id', $player->id)
            ->whereIn('category', $categoriesToLoad)
            ->get();
        
        $registrations = TournamentRegistration::where('player_id', $player->id)
            ->whereHas('tournament', function($query) {
                $query->where('status', TournamentStatus::COMPLETED->value);
            })
            ->with(['tournament', 'category'])
            ->latest()
            ->get();
        
        $skillLevel = null;
        $isProvisional = false;
        $primaryCategory = $player->gender === 'Female' ? CategoryType::WOMENS_SINGLES->value : CategoryType::MENS_SINGLES->value;
        $primaryElo = $eloRatings->where('category', $primaryCategory)->first();
        
        if ($clubMembership) {
            $skillLevel = $clubMembership->skill_level;
            $isProvisional = $clubMembership->is_provisional;
            
            if ($primaryElo && $primaryElo->matches_played > 0) {
                $skillLevel = \App\Helpers\SkillLevelHelper::convertEloToSkillLevel($primaryElo->current_rating);
                $isProvisional = false;
            }
        }
        
        return view('players.show', [
            'player' => $player,
            'club' => $club,
            'age' => $age,
            'totalMatches' => $stats['total_matches'] ?? 0,
            'wins' => $stats['wins'] ?? 0,
            'losses' => $stats['losses'] ?? 0,
            'winRate' => $stats['win_rate'] ?? 0,
            'categoryStats' => $stats['category_stats'] ?? [],
            'recentWinRate' => $stats['recent_win_rate'] ?? 0,
            'recentWins' => $stats['recent_wins'] ?? 0,
            'recentLosses' => $stats['recent_losses'] ?? 0,
            'averagePoints' => $stats['average_points'] ?? 0,
            'tournamentStats' => $stats['tournament_stats'] ?? ['tournaments_joined' => 0, 'tournaments_completed' => 0, 'best_finish' => 'N/A'],
            'eloRatings' => $stats['elo_ratings'] ?? collect([]),
            'registrations' => $registrations,
            'rankingHistory' => $stats['ranking_history'] ?? collect([]),
            'recentMatches' => $recentMatches,
            'skillLevel' => $skillLevel,
            'isProvisional' => $isProvisional,
            'primaryElo' => $primaryElo,
            'primaryCategory' => $primaryCategory,
        ]);
    }
    
    public function showOther(User $user): View
    {
        if (auth()->check() && auth()->user() && auth()->user()->isManager()) {
            return $this->showForManager($user);
        }
        
        $player = $user;
        $clubMembership = $player->approvedClubMembership;
        $club = $clubMembership ? $clubMembership->club : null;

        $age = null;
        try {
            if ($player->birth_year && $player->birth_month && $player->birth_day) {
                $birthDate = Carbon::createFromDate($player->birth_year, $player->birth_month, $player->birth_day);
                $age = (int)$birthDate->diffInYears(Carbon::now());
            }
        } catch (InvalidFormatException|\Exception $e) {
            \Log::error("Invalid birth date for player {$player->id} in showOther: " . $e->getMessage());
            $age = null;
        }
        
        $statisticsService = app(StatisticsService::class);
        $stats = $statisticsService->getPlayerStatistics($player);
        
        $matches = TournamentMatch::where(function($query) use ($player) {
            $query->where('player1_id', $player->id)
                  ->orWhere('player2_id', $player->id)
                  ->orWhere('player1_partner_id', $player->id)
                  ->orWhere('player2_partner_id', $player->id);
        })->with(['result', 'tournament', 'category', 'player1', 'player2', 'player1Partner', 'player2Partner'])->get();
        
        $recentMatches = $matches->filter(function($match) {
            return $match->status === MatchStatus::COMPLETED->value && $match->result;
        })->sortByDesc('updated_at')->take(10);
        
        $gender = $player->gender ?? 'Male';
        $categoriesToLoad = $gender === 'Female' 
            ? [CategoryType::WOMENS_SINGLES->value, CategoryType::WOMENS_DOUBLES->value, CategoryType::MIXED_DOUBLES->value]
            : [CategoryType::MENS_SINGLES->value, CategoryType::MENS_DOUBLES->value, CategoryType::MIXED_DOUBLES->value];
        
        $eloRatings = EloRating::where('player_id', $player->id)
            ->whereIn('category', $categoriesToLoad)
            ->get();
        
        $registrations = TournamentRegistration::where('player_id', $player->id)
            ->whereHas('tournament', function($query) {
                $query->where('status', TournamentStatus::COMPLETED->value);
            })
            ->with(['tournament', 'category'])
            ->latest()
            ->get();
        
        $skillLevel = null;
        $isProvisional = false;
        $primaryCategory = $player->gender === 'Female' ? CategoryType::WOMENS_SINGLES->value : CategoryType::MENS_SINGLES->value;
        $primaryElo = $eloRatings->where('category', $primaryCategory)->first();
        
        if ($clubMembership) {
            $skillLevel = $clubMembership->skill_level;
            $isProvisional = $clubMembership->is_provisional;
            
            if ($primaryElo && $primaryElo->matches_played > 0) {
                $skillLevel = \App\Helpers\SkillLevelHelper::convertEloToSkillLevel($primaryElo->current_rating);
                $isProvisional = false;
            }
        }

        return view('players.show', [
            'player' => $player,
            'club' => $club,
            'age' => $age,
            'totalMatches' => $stats['total_matches'],
            'wins' => $stats['wins'],
            'losses' => $stats['losses'],
            'winRate' => $stats['win_rate'],
            'categoryStats' => $stats['category_stats'],
            'recentWinRate' => $stats['recent_win_rate'],
            'recentWins' => $stats['recent_wins'],
            'recentLosses' => $stats['recent_losses'],
            'averagePoints' => $stats['average_points'],
            'tournamentStats' => $stats['tournament_stats'],
            'eloRatings' => $stats['elo_ratings'],
            'registrations' => $registrations,
            'rankingHistory' => $stats['ranking_history'],
            'recentMatches' => $recentMatches,
            'skillLevel' => $skillLevel,
            'isProvisional' => $isProvisional,
            'primaryElo' => $primaryElo,
            'primaryCategory' => $primaryCategory,
        ]);
    }
    
    /**
     * Show player profile for managers (uses manager layout).
     */
    private function showForManager(User $user): View
    {
        $player = $user;
        $clubMembership = $player->approvedClubMembership;
        $club = $clubMembership ? $clubMembership->club : null;
        
        $age = null;
        try {
            if ($player->birth_year && $player->birth_month && $player->birth_day) {
                $birthDate = Carbon::createFromDate($player->birth_year, $player->birth_month, $player->birth_day);
                $age = (int)$birthDate->diffInYears(Carbon::now());
            }
        } catch (InvalidFormatException|\Exception $e) {
            \Log::error("Invalid birth date for player {$player->id}: " . $e->getMessage());
            $age = null;
        }
        
        try {
            $statisticsService = app(StatisticsService::class);
            $stats = $statisticsService->getPlayerStatistics($player);
        } catch (\Exception $e) {
            \Log::error("Error fetching player statistics for player {$player->id} in showForManager: " . $e->getMessage());
            $stats = [
                'total_matches' => 0,
                'wins' => 0,
                'losses' => 0,
                'win_rate' => 0,
                'category_stats' => [],
                'recent_win_rate' => 0,
                'recent_wins' => 0,
                'recent_losses' => 0,
                'average_points' => 0,
                'tournament_stats' => ['tournaments_joined' => 0, 'tournaments_completed' => 0, 'best_finish' => 'N/A'],
                'elo_ratings' => collect([]),
                'ranking_history' => collect([]),
            ];
        }
        
        $matches = TournamentMatch::where(function($query) use ($player) {
            $query->where('player1_id', $player->id)
                  ->orWhere('player2_id', $player->id)
                  ->orWhere('player1_partner_id', $player->id)
                  ->orWhere('player2_partner_id', $player->id);
        })->with(['result', 'tournament', 'category', 'player1', 'player2', 'player1Partner', 'player2Partner'])->get();
        
        $recentMatches = $matches->filter(function($match) {
            return $match->status === MatchStatus::COMPLETED->value && $match->result;
        })->sortByDesc(function($match) {
            return $match->updated_at ?? $match->created_at;
        })->take(10);
        
        $gender = $player->gender ?? 'Male';
        $categoriesToLoad = $gender === 'Female' 
            ? [CategoryType::WOMENS_SINGLES->value, CategoryType::WOMENS_DOUBLES->value, CategoryType::MIXED_DOUBLES->value]
            : [CategoryType::MENS_SINGLES->value, CategoryType::MENS_DOUBLES->value, CategoryType::MIXED_DOUBLES->value];
        
        $eloRatings = EloRating::where('player_id', $player->id)
            ->whereIn('category', $categoriesToLoad)
            ->get();

        $registrations = TournamentRegistration::where('player_id', $player->id)
            ->whereHas('tournament', function($query) {
                $query->where('status', TournamentStatus::COMPLETED->value);
            })
            ->with(['tournament', 'category'])
            ->latest()
            ->get();

        $skillLevel = null;
        $isProvisional = false;
        $primaryCategory = $player->gender === 'Female' ? CategoryType::WOMENS_SINGLES->value : CategoryType::MENS_SINGLES->value;
        $primaryElo = $eloRatings->where('category', $primaryCategory)->first();

        if ($clubMembership) {
            $skillLevel = $clubMembership->skill_level;
            $isProvisional = $clubMembership->is_provisional;

            if ($primaryElo && $primaryElo->matches_played > 0) {
                $skillLevel = \App\Helpers\SkillLevelHelper::convertEloToSkillLevel($primaryElo->current_rating);
                $isProvisional = false;
            }
        }

        return view('manager.players.show', [
            'player' => $player,
            'club' => $club,
            'age' => $age,
            'totalMatches' => $stats['total_matches'] ?? 0,
            'wins' => $stats['wins'] ?? 0,
            'losses' => $stats['losses'] ?? 0,
            'winRate' => $stats['win_rate'] ?? 0,
            'categoryStats' => $stats['category_stats'] ?? [],
            'recentWinRate' => $stats['recent_win_rate'] ?? 0,
            'recentWins' => $stats['recent_wins'] ?? 0,
            'recentLosses' => $stats['recent_losses'] ?? 0,
            'averagePoints' => $stats['average_points'] ?? 0,
            'tournamentStats' => $stats['tournament_stats'] ?? ['tournaments_joined' => 0, 'tournaments_completed' => 0, 'best_finish' => 'N/A'],
            'eloRatings' => $stats['elo_ratings'] ?? collect([]),
            'registrations' => $registrations,
            'rankingHistory' => $stats['ranking_history'] ?? collect([]),
            'recentMatches' => $recentMatches,
            'skillLevel' => $skillLevel,
            'isProvisional' => $isProvisional,
            'primaryElo' => $primaryElo,
            'primaryCategory' => $primaryCategory,
        ]);
    }
    
}
