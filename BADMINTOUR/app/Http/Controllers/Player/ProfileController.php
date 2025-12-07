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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            'gender' => 'nullable|in:male,female,other',
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
            'id_type' => 'nullable|in:drivers_license,student_id,passport,national_id,prc_id,postal_id,senior_citizen_id,others',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'player_id_document' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
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
        
        // Calculate age from birth_month, birth_day, birth_year fields (ensure integer)
        $age = null;
        if ($player->birth_year && $player->birth_month && $player->birth_day) {
            $birthDate = Carbon::createFromDate($player->birth_year, $player->birth_month, $player->birth_day);
            $age = (int)$birthDate->diffInYears(Carbon::now());
        }
        
        // Get all matches where player participated (including as partner)
        $matches = TournamentMatch::where(function($query) use ($player) {
            $query->where('player1_id', $player->id)
                  ->orWhere('player2_id', $player->id)
                  ->orWhere('player1_partner_id', $player->id)
                  ->orWhere('player2_partner_id', $player->id);
        })->with(['result', 'tournament', 'category'])->get();
        
        // Count wins and losses (accounting for doubles partners)
        $wins = 0;
        $losses = 0;
        
        foreach ($matches as $match) {
            if (!$match->result || !$match->winner_id) {
                continue; // Skip matches without results
            }
            
            $isDoubles = $match->player1_partner_id || $match->player2_partner_id;
            
            // Check if player won
            $playerWon = false;
            if ($isDoubles && $match->winner_partner_id) {
                // Doubles: check if player or partner won
                $playerWon = ($match->winner_id === $player->id || $match->winner_partner_id === $player->id);
            } else {
                // Singles: check if player won
                $playerWon = ($match->winner_id === $player->id);
            }
            
            if ($playerWon) {
                $wins++;
            } else {
                // Player lost - check if player participated in this match
                $playerParticipated = (
                    $match->player1_id === $player->id ||
                    $match->player2_id === $player->id ||
                    $match->player1_partner_id === $player->id ||
                    $match->player2_partner_id === $player->id
                );
                
                if ($playerParticipated) {
                    $losses++;
                }
            }
        }
        
        $totalMatches = $wins + $losses;
        $winRate = $totalMatches > 0 ? round(($wins / $totalMatches) * 100, 1) : 0;
        
        // Category-specific statistics
        $categoryStats = $this->calculateCategoryStatistics($matches, $player);
        
        // Recent performance (last 10 matches)
        $recentMatches = $matches->filter(function($match) {
            return $match->status === 'completed' && $match->result;
        })->sortByDesc('updated_at')->take(10);
        
        $recentWins = 0;
        $recentLosses = 0;
        foreach ($recentMatches as $match) {
            if (!$match->result || !$match->winner_id) continue;
            
            $isDoubles = $match->player1_partner_id || $match->player2_partner_id;
            $playerWon = false;
            if ($isDoubles && $match->winner_partner_id) {
                $playerWon = ($match->winner_id === $player->id || $match->winner_partner_id === $player->id);
            } else {
                $playerWon = ($match->winner_id === $player->id);
            }
            
            if ($playerWon) {
                $recentWins++;
            } else {
                $recentLosses++;
            }
        }
        $recentWinRate = ($recentWins + $recentLosses) > 0 ? round(($recentWins / ($recentWins + $recentLosses)) * 100, 1) : 0;
        
        // Average points per match
        $averagePoints = $this->calculateAveragePoints($matches, $player);
        
        // Tournament participation statistics
        $tournamentStats = $this->calculateTournamentStatistics($player);
        
        $eloRatings = EloRating::where('player_id', $player->id)->get();
        
        $registrations = TournamentRegistration::where('player_id', $player->id)
            ->with(['tournament', 'category'])
            ->latest()
            ->take(10)
            ->get();
        
        $rankingHistory = RankingHistory::where('player_id', $player->id)
            ->with('tournament')
            ->orderBy('recorded_at', 'desc')
            ->take(20)
            ->get();
        
        // Get skill level from club membership
        $skillLevel = null;
        $isProvisional = false;
        $primaryCategory = $player->gender === 'Female' ? 'WS' : 'MS';
        $primaryElo = $eloRatings->where('category', $primaryCategory)->first();
        
        if ($clubMembership) {
            $skillLevel = $clubMembership->skill_level;
            $isProvisional = $clubMembership->is_provisional;
            
            // If player has official ranking, calculate skill level from ELO
            if ($primaryElo && $primaryElo->matches_played > 0) {
                $skillLevel = \App\Helpers\SkillLevelHelper::convertEloToSkillLevel($primaryElo->current_rating);
                $isProvisional = false; // Official ranking means not provisional
            }
        }
        
        return view('players.show', compact(
            'player',
            'club',
            'age',
            'totalMatches',
            'wins',
            'losses',
            'winRate',
            'categoryStats',
            'recentWinRate',
            'recentWins',
            'recentLosses',
            'averagePoints',
            'tournamentStats',
            'eloRatings',
            'registrations',
            'rankingHistory',
            'recentMatches',
            'skillLevel',
            'isProvisional',
            'primaryElo',
            'primaryCategory'
        ));
    }
    
    public function showOther(User $user): View
    {
        // Both players and managers can view player profiles
        // Players can view other players' profiles (but not biodata)
        // Managers can view player profiles (but biodata is only available through the modal in My Club)
        
        // If manager, use manager-specific view
        if (auth()->user()->isManager()) {
            return $this->showForManager($user);
        }
        
        $player = $user;
        
        $clubMembership = $player->approvedClubMembership;
        $club = $clubMembership ? $clubMembership->club : null;
        
        // Calculate age from birth_month, birth_day, birth_year fields (ensure integer)
        $age = null;
        if ($player->birth_year && $player->birth_month && $player->birth_day) {
            $birthDate = Carbon::createFromDate($player->birth_year, $player->birth_month, $player->birth_day);
            $age = (int)$birthDate->diffInYears(Carbon::now());
        }
        
        // Get all matches where player participated (including as partner)
        $matches = TournamentMatch::where(function($query) use ($player) {
            $query->where('player1_id', $player->id)
                  ->orWhere('player2_id', $player->id)
                  ->orWhere('player1_partner_id', $player->id)
                  ->orWhere('player2_partner_id', $player->id);
        })->with(['result', 'tournament', 'category'])->get();
        
        // Count wins and losses (accounting for doubles partners)
        $wins = 0;
        $losses = 0;
        
        foreach ($matches as $match) {
            if (!$match->result || !$match->winner_id) {
                continue; // Skip matches without results
            }
            
            $isDoubles = $match->player1_partner_id || $match->player2_partner_id;
            
            // Check if player won
            $playerWon = false;
            if ($isDoubles && $match->winner_partner_id) {
                // Doubles: check if player or partner won
                $playerWon = ($match->winner_id === $player->id || $match->winner_partner_id === $player->id);
            } else {
                // Singles: check if player won
                $playerWon = ($match->winner_id === $player->id);
            }
            
            if ($playerWon) {
                $wins++;
            } else {
                // Player lost - check if player participated in this match
                $playerParticipated = (
                    $match->player1_id === $player->id ||
                    $match->player2_id === $player->id ||
                    $match->player1_partner_id === $player->id ||
                    $match->player2_partner_id === $player->id
                );
                
                if ($playerParticipated) {
                    $losses++;
                }
            }
        }
        
        $totalMatches = $wins + $losses;
        $winRate = $totalMatches > 0 ? round(($wins / $totalMatches) * 100, 1) : 0;
        
        // Category-specific statistics
        $categoryStats = $this->calculateCategoryStatistics($matches, $player);
        
        // Recent performance (last 10 matches)
        $recentMatches = $matches->filter(function($match) {
            return $match->status === 'completed' && $match->result;
        })->sortByDesc('updated_at')->take(10);
        
        $recentWins = 0;
        $recentLosses = 0;
        foreach ($recentMatches as $match) {
            if (!$match->result || !$match->winner_id) continue;
            
            $isDoubles = $match->player1_partner_id || $match->player2_partner_id;
            $playerWon = false;
            if ($isDoubles && $match->winner_partner_id) {
                $playerWon = ($match->winner_id === $player->id || $match->winner_partner_id === $player->id);
            } else {
                $playerWon = ($match->winner_id === $player->id);
            }
            
            if ($playerWon) {
                $recentWins++;
            } else {
                $recentLosses++;
            }
        }
        $recentWinRate = ($recentWins + $recentLosses) > 0 ? round(($recentWins / ($recentWins + $recentLosses)) * 100, 1) : 0;
        
        // Average points per match
        $averagePoints = $this->calculateAveragePoints($matches, $player);
        
        // Tournament participation statistics
        $tournamentStats = $this->calculateTournamentStatistics($player);
        
        $eloRatings = EloRating::where('player_id', $player->id)->get();
        
        $registrations = TournamentRegistration::where('player_id', $player->id)
            ->with(['tournament', 'category'])
            ->latest()
            ->take(10)
            ->get();
        
        $rankingHistory = RankingHistory::where('player_id', $player->id)
            ->with('tournament')
            ->orderBy('recorded_at', 'desc')
            ->take(20)
            ->get();
        
        // Get skill level from club membership
        $skillLevel = null;
        $isProvisional = false;
        $primaryCategory = $player->gender === 'Female' ? 'WS' : 'MS';
        $primaryElo = $eloRatings->where('category', $primaryCategory)->first();
        
        if ($clubMembership) {
            $skillLevel = $clubMembership->skill_level;
            $isProvisional = $clubMembership->is_provisional;
            
            // If player has official ranking, calculate skill level from ELO
            if ($primaryElo && $primaryElo->matches_played > 0) {
                $skillLevel = \App\Helpers\SkillLevelHelper::convertEloToSkillLevel($primaryElo->current_rating);
                $isProvisional = false; // Official ranking means not provisional
            }
        }
        
        return view('players.show', compact(
            'player',
            'club',
            'age',
            'totalMatches',
            'wins',
            'losses',
            'winRate',
            'categoryStats',
            'recentWinRate',
            'recentWins',
            'recentLosses',
            'averagePoints',
            'tournamentStats',
            'eloRatings',
            'registrations',
            'rankingHistory',
            'recentMatches',
            'skillLevel',
            'isProvisional',
            'primaryElo',
            'primaryCategory'
        ));
    }
    
    /**
     * Show player profile for managers (uses manager layout).
     */
    private function showForManager(User $user): View
    {
        $player = $user;
        
        $clubMembership = $player->approvedClubMembership;
        $club = $clubMembership ? $clubMembership->club : null;
        
        // Calculate age from birth_month, birth_day, birth_year fields (ensure integer)
        $age = null;
        if ($player->birth_year && $player->birth_month && $player->birth_day) {
            $birthDate = Carbon::createFromDate($player->birth_year, $player->birth_month, $player->birth_day);
            $age = (int)$birthDate->diffInYears(Carbon::now());
        }
        
        // Get all matches where player participated (including as partner)
        $matches = TournamentMatch::where(function($query) use ($player) {
            $query->where('player1_id', $player->id)
                  ->orWhere('player2_id', $player->id)
                  ->orWhere('player1_partner_id', $player->id)
                  ->orWhere('player2_partner_id', $player->id);
        })->with(['result', 'tournament', 'category'])->get();
        
        // Count wins and losses (accounting for doubles partners)
        $wins = 0;
        $losses = 0;
        
        foreach ($matches as $match) {
            if (!$match->result || !$match->winner_id) {
                continue; // Skip matches without results
            }
            
            $isDoubles = $match->player1_partner_id || $match->player2_partner_id;
            
            // Check if player won
            $playerWon = false;
            if ($isDoubles && $match->winner_partner_id) {
                // Doubles: check if player or partner won
                $playerWon = ($match->winner_id === $player->id || $match->winner_partner_id === $player->id);
            } else {
                // Singles: check if player won
                $playerWon = ($match->winner_id === $player->id);
            }
            
            if ($playerWon) {
                $wins++;
            } else {
                // Player lost - check if player participated in this match
                $playerParticipated = (
                    $match->player1_id === $player->id ||
                    $match->player2_id === $player->id ||
                    $match->player1_partner_id === $player->id ||
                    $match->player2_partner_id === $player->id
                );
                
                if ($playerParticipated) {
                    $losses++;
                }
            }
        }
        
        $totalMatches = $wins + $losses;
        $winRate = $totalMatches > 0 ? round(($wins / $totalMatches) * 100, 1) : 0;
        
        // Category-specific statistics
        $categoryStats = $this->calculateCategoryStatistics($matches, $player);
        
        // Recent performance (last 10 matches)
        $recentMatches = $matches->filter(function($match) {
            return $match->status === 'completed' && $match->result;
        })->sortByDesc(function($match) {
            return $match->updated_at ?? $match->created_at;
        })->take(10);
        
        $recentWins = 0;
        $recentLosses = 0;
        foreach ($recentMatches as $match) {
            if (!$match->result || !$match->winner_id) continue;
            
            $isDoubles = $match->player1_partner_id || $match->player2_partner_id;
            $playerWon = false;
            if ($isDoubles && $match->winner_partner_id) {
                $playerWon = ($match->winner_id === $player->id || $match->winner_partner_id === $player->id);
            } else {
                $playerWon = ($match->winner_id === $player->id);
            }
            
            if ($playerWon) {
                $recentWins++;
            } else {
                $recentLosses++;
            }
        }
        $recentWinRate = ($recentWins + $recentLosses) > 0 ? round(($recentWins / ($recentWins + $recentLosses)) * 100, 1) : 0;
        
        // Average points per match
        $averagePoints = $this->calculateAveragePoints($matches, $player);
        
        // Tournament participation statistics
        $tournamentStats = $this->calculateTournamentStatistics($player);
        
        $eloRatings = EloRating::where('player_id', $player->id)->get();
        
        $registrations = TournamentRegistration::where('player_id', $player->id)
            ->with(['tournament', 'category'])
            ->latest()
            ->take(10)
            ->get();
        
        $rankingHistory = RankingHistory::where('player_id', $player->id)
            ->with('tournament')
            ->orderBy('recorded_at', 'desc')
            ->take(20)
            ->get();
        
        // Get skill level from club membership
        $skillLevel = null;
        $isProvisional = false;
        $primaryCategory = $player->gender === 'Female' ? 'WS' : 'MS';
        $primaryElo = $eloRatings->where('category', $primaryCategory)->first();
        
        if ($clubMembership) {
            $skillLevel = $clubMembership->skill_level;
            $isProvisional = $clubMembership->is_provisional;
            
            // If player has official ranking, calculate skill level from ELO
            if ($primaryElo && $primaryElo->matches_played > 0) {
                $skillLevel = \App\Helpers\SkillLevelHelper::convertEloToSkillLevel($primaryElo->current_rating);
                $isProvisional = false; // Official ranking means not provisional
            }
        }
        
        return view('manager.players.show', compact(
            'player',
            'club',
            'age',
            'totalMatches',
            'wins',
            'losses',
            'winRate',
            'categoryStats',
            'recentWinRate',
            'recentWins',
            'recentLosses',
            'averagePoints',
            'tournamentStats',
            'eloRatings',
            'registrations',
            'rankingHistory',
            'recentMatches',
            'skillLevel',
            'isProvisional',
            'primaryElo',
            'primaryCategory'
        ));
    }
    
    /**
     * Calculate category-specific statistics (wins, losses, win rate per category)
     */
    private function calculateCategoryStatistics($matches, User $player): array
    {
        $categoryStats = [
            'MS' => ['wins' => 0, 'losses' => 0, 'matches' => 0, 'winRate' => 0],
            'WS' => ['wins' => 0, 'losses' => 0, 'matches' => 0, 'winRate' => 0],
            'MD' => ['wins' => 0, 'losses' => 0, 'matches' => 0, 'winRate' => 0],
            'WD' => ['wins' => 0, 'losses' => 0, 'matches' => 0, 'winRate' => 0],
            'XD' => ['wins' => 0, 'losses' => 0, 'matches' => 0, 'winRate' => 0],
        ];
        
        foreach ($matches as $match) {
            if (!$match->result || !$match->winner_id || !$match->category) {
                continue;
            }
            
            $categoryType = $match->category->type ?? 'MS';
            if (!isset($categoryStats[$categoryType])) {
                continue;
            }
            
            $isDoubles = $match->player1_partner_id || $match->player2_partner_id;
            $playerWon = false;
            if ($isDoubles && $match->winner_partner_id) {
                $playerWon = ($match->winner_id === $player->id || $match->winner_partner_id === $player->id);
            } else {
                $playerWon = ($match->winner_id === $player->id);
            }
            
            $categoryStats[$categoryType]['matches']++;
            if ($playerWon) {
                $categoryStats[$categoryType]['wins']++;
            } else {
                $categoryStats[$categoryType]['losses']++;
            }
        }
        
        // Calculate win rates
        foreach ($categoryStats as $category => &$stats) {
            if ($stats['matches'] > 0) {
                $stats['winRate'] = round(($stats['wins'] / $stats['matches']) * 100, 1);
            }
        }
        
        return $categoryStats;
    }
    
    /**
     * Calculate average points per match
     */
    private function calculateAveragePoints($matches, User $player): float
    {
        $totalPoints = 0;
        $totalSets = 0;
        
        foreach ($matches as $match) {
            if (!$match->result) {
                continue;
            }
            
            $result = $match->result;
            $isPlayer1 = ($match->player1_id === $player->id || $match->player1_partner_id === $player->id);
            
            // Count points from all sets
            if ($result->player1_set1_score !== null) {
                $totalPoints += $isPlayer1 ? $result->player1_set1_score : $result->player2_set1_score;
                $totalSets++;
            }
            if ($result->player1_set2_score !== null) {
                $totalPoints += $isPlayer1 ? $result->player1_set2_score : $result->player2_set2_score;
                $totalSets++;
            }
            if ($result->player1_set3_score !== null) {
                $totalPoints += $isPlayer1 ? $result->player1_set3_score : $result->player2_set3_score;
                $totalSets++;
            }
        }
        
        return $totalSets > 0 ? round($totalPoints / $totalSets, 1) : 0;
    }
    
    /**
     * Calculate tournament participation statistics
     */
    private function calculateTournamentStatistics(User $player): array
    {
        $registrations = TournamentRegistration::where('player_id', $player->id)
            ->with(['tournament', 'category'])
            ->get();
        
        $tournamentsJoined = $registrations->pluck('tournament_id')->unique()->count();
        
        // Get completed tournaments (tournaments with status 'completed')
        $completedTournaments = $registrations->filter(function($reg) {
            return $reg->tournament && $reg->tournament->status === 'completed';
        })->pluck('tournament_id')->unique()->count();
        
        // Find best finish (furthest round reached)
        $bestFinish = null;
        $roundPriority = [
            'Finals' => 1,
            'Semifinals' => 2,
            'Quarterfinals' => 3,
            'Round of 16' => 4,
            'Round of 32' => 5,
            'Round of 64' => 6,
            'Second Round' => 7,
            'First Round' => 8,
        ];
        
        $playerMatches = TournamentMatch::where(function($query) use ($player) {
            $query->where('player1_id', $player->id)
                  ->orWhere('player2_id', $player->id)
                  ->orWhere('player1_partner_id', $player->id)
                  ->orWhere('player2_partner_id', $player->id);
        })
        ->where('status', 'completed')
        ->whereNotNull('round')
        ->with(['tournament', 'category'])
        ->get();
        
        foreach ($playerMatches as $match) {
            if (!$match->tournament || $match->tournament->status !== 'completed') {
                continue;
            }
            
            // Get round name using TournamentRoundHelper
            $roundName = \App\Helpers\TournamentRoundHelper::getRoundName(
                $match->tournament->bracket_type ?? 'single_elimination',
                (int)$match->round,
                $this->getMaxRounds($match->tournament)
            );
            
            // Normalize round name (handle "Round N" format)
            $normalizedRoundName = $roundName;
            if (preg_match('/^Round (\d+)$/', $roundName, $matches)) {
                $roundNum = (int)$matches[1];
                // For early rounds, use lower priority
                $normalizedRoundName = $roundNum <= 2 ? "Round {$roundNum}" : $roundName;
            }
            
            // Check if this is a better finish
            if (!$bestFinish) {
                $bestFinish = $normalizedRoundName;
            } else {
                $currentPriority = $roundPriority[$normalizedRoundName] ?? 999;
                $bestPriority = $roundPriority[$bestFinish] ?? 999;
                if ($currentPriority < $bestPriority) {
                    $bestFinish = $normalizedRoundName;
                }
            }
        }
        
        return [
            'tournamentsJoined' => $tournamentsJoined,
            'tournamentsCompleted' => $completedTournaments,
            'bestFinish' => $bestFinish ?? 'N/A',
        ];
    }
    
    /**
     * Get maximum rounds for a tournament
     */
    private function getMaxRounds($tournament): int
    {
        // Get the maximum number of participants from categories
        $maxParticipants = TournamentCategory::where('tournament_id', $tournament->id)
            ->max('max_participants') ?? 32;
        
        // Calculate rounds based on bracket type
        if ($tournament->bracket_type === 'round_robin') {
            return $maxParticipants - 1;
        }
        
        // Single elimination: log2(participants)
        return (int)ceil(log($maxParticipants, 2));
    }
}
