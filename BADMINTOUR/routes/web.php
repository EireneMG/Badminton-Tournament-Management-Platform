<?php

use App\Http\Controllers\ProfileController;
use App\Models\TournamentMatch;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('landing');

// Registration and Login flow
Route::middleware('guest')->group(function () {
    // Registration selection
    Route::get('/register', function () {
        return view('auth.register-select');
    })->name('register');
    
    Route::get('/register/player', function () {
        return view('auth.register-player');
    })->name('register.player');
    
    Route::get('/register/manager', function () {
        return view('auth.register-manager');
    })->name('register.manager');
    
    // Login selection
    Route::get('/login-select', function () {
        return view('auth.login-select');
    })->name('login.select');
    
    // Manager login page
    Route::get('/login/manager', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'createManager'])
        ->name('login.manager');
    
    // Manager login POST
    Route::post('/login/manager', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'storeManager'])
        ->name('login.manager.store');
});

// Generic dashboard route - redirects to role-specific dashboard
Route::get('/dashboard', function () {
    $dashboardRoute = auth()->user()->getDashboardRoute();
    return redirect()->route($dashboardRoute);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/player/dashboard', [\App\Http\Controllers\Player\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'player', 'biodata.completed'])->name('player.dashboard');

Route::get('/manager/dashboard', [\App\Http\Controllers\Manager\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'manager'])->name('manager.dashboard');

Route::middleware('auth')->group(function () {
    // Manager Onboarding Routes
    Route::get('/manager/verify-id', [\App\Http\Controllers\Manager\VerificationController::class, 'create'])
        ->middleware('manager')->name('manager.verify-id');
    
    Route::post('/manager/verify-id', [\App\Http\Controllers\Manager\VerificationController::class, 'store'])
        ->middleware('manager')->name('manager.verify-id.submit');
    
    Route::get('/manager/create-club', [\App\Http\Controllers\Manager\ClubController::class, 'create'])
        ->middleware('manager')->name('manager.create-club');
    
    Route::post('/manager/create-club', [\App\Http\Controllers\Manager\ClubController::class, 'store'])
        ->middleware('manager')->name('manager.create-club.submit');
    
    // Club Join System Routes (Manager approval/rejection)
    Route::post('/club-players/{clubPlayer}/approve', [\App\Http\Controllers\Player\ClubJoinController::class, 'approve'])
        ->middleware('manager')->name('club-players.approve');
    
    Route::post('/club-players/{clubPlayer}/reject', [\App\Http\Controllers\Player\ClubJoinController::class, 'reject'])
        ->middleware('manager')->name('club-players.reject');
    
    // Public/read-only access for manager tournament index/show so real users can view sample tournaments
    Route::prefix('manager')->name('manager.')->group(function () {
        Route::get('tournaments', [\App\Http\Controllers\Manager\TournamentController::class, 'index'])
            ->name('tournaments.index');
        Route::get('tournaments/{tournament}', [\App\Http\Controllers\Manager\TournamentController::class, 'show'])
            ->whereNumber('tournament')
            ->name('tournaments.show');
    });

    // Manager Tournament System Routes (protected actions only)
    Route::middleware(['manager', 'verified', 'verifiedId', 'hasClub'])->prefix('manager')->name('manager.')->group(function () {
        // Tournament CRUD (exclude index/show as they are public read-only above)
        Route::get('tournaments/generate', [\App\Http\Controllers\Manager\TournamentController::class, 'generate'])
            ->name('tournaments.generate');
        Route::resource('tournaments', \App\Http\Controllers\Manager\TournamentController::class)->except(['index', 'show', 'edit', 'update']);
        Route::post('tournaments/{tournament}/reschedule', [\App\Http\Controllers\Manager\TournamentController::class, 'reschedule'])
            ->name('tournaments.reschedule');
        Route::post('tournaments/{tournament}/generate-matches', [\App\Http\Controllers\Manager\TournamentController::class, 'generateMatches'])
            ->name('tournaments.generate-matches');
        Route::post('matches/{match}/record-result', [\App\Http\Controllers\Manager\TournamentController::class, 'recordResult'])
            ->name('matches.record-result');
        
        Route::post('registrations/{registration}/update-status', [\App\Http\Controllers\Manager\TournamentRegistrationController::class, 'updateStatus'])
            ->name('registrations.update-status');
        Route::post('registrations/bulk-update-status', [\App\Http\Controllers\Manager\TournamentRegistrationController::class, 'bulkUpdateStatus'])
            ->name('registrations.bulk-update-status');
        
        // Match Management
        Route::post('matches/{match}/update-score', [\App\Http\Controllers\Manager\MatchController::class, 'updateScore'])
            ->name('matches.update-score');
        Route::post('matches/{match}/walkover', [\App\Http\Controllers\Manager\MatchController::class, 'markWalkover'])
            ->name('matches.walkover');
        Route::post('matches/{match}/reschedule', [\App\Http\Controllers\Manager\MatchController::class, 'reschedule'])
            ->name('matches.reschedule');
        
        // Withdrawal Management
        Route::get('withdrawals', [\App\Http\Controllers\Manager\WithdrawalController::class, 'index'])
            ->name('withdrawals.index');
        Route::get('withdrawals/{withdrawalRequest}', [\App\Http\Controllers\Manager\WithdrawalController::class, 'show'])
            ->name('withdrawals.show');
        Route::post('withdrawals/{withdrawalRequest}/approve', [\App\Http\Controllers\Manager\WithdrawalController::class, 'approve'])
            ->name('withdrawals.approve');
        Route::post('withdrawals/{withdrawalRequest}/reject', [\App\Http\Controllers\Manager\WithdrawalController::class, 'reject'])
            ->name('withdrawals.reject');
        
        Route::get('statistics/export/{format?}', [\App\Http\Controllers\Manager\StatisticsExportController::class, 'exportManagerStatistics'])
            ->name('statistics.export');
        Route::get('club/statistics/export/{format?}', [\App\Http\Controllers\Manager\StatisticsExportController::class, 'exportClubStatistics'])
            ->name('club.statistics.export');
    });
    
    // Player Tournament System Routes
    Route::middleware(['player', 'verified', 'biodata.completed'])->prefix('player')->group(function () {
        // Tournament Registration
        Route::get('tournaments/{tournament}/register/{category}', [\App\Http\Controllers\Player\TournamentController::class, 'showRegistration'])
            ->name('player.tournaments.register.show');
        Route::post('tournaments/register', [\App\Http\Controllers\Player\TournamentRegistrationController::class, 'register'])
            ->name('player.tournaments.register');
        Route::delete('registrations/{registration}/cancel', [\App\Http\Controllers\Player\TournamentRegistrationController::class, 'cancel'])
            ->name('player.registrations.cancel');
        Route::post('registrations/{registration}/withdraw', [\App\Http\Controllers\Player\TournamentRegistrationController::class, 'withdraw'])
            ->name('player.registrations.withdraw');
        
        // Player-specific tournament views
        Route::get('tournaments/{tournament}', [\App\Http\Controllers\Player\TournamentController::class, 'show'])
            ->name('player.tournaments.show');
        
        Route::get('matches/{match}', function (TournamentMatch $match) {
            return view('matches.show', ['match' => $match]);
        })->name('player.matches.show');
        
        // Partner Invitations
        Route::post('invitations/send', [\App\Http\Controllers\Player\PartnerInvitationController::class, 'send'])
            ->name('player.invitations.send');
        Route::post('invitations/{invitation}/accept', [\App\Http\Controllers\Player\PartnerInvitationController::class, 'accept'])
            ->name('player.invitations.accept');
        Route::post('invitations/{invitation}/reject', [\App\Http\Controllers\Player\PartnerInvitationController::class, 'reject'])
            ->name('player.invitations.reject');
        Route::post('invitations/{invitation}/cancel', [\App\Http\Controllers\Player\PartnerInvitationController::class, 'cancel'])
            ->name('player.invitations.cancel');
        Route::get('invitations/{invitation}', [\App\Http\Controllers\Player\PartnerInvitationController::class, 'show'])
            ->name('player.invitations.show');
        
        // Invitation Management
        Route::get('invitations', [\App\Http\Controllers\Player\InvitationController::class, 'index'])
            ->name('player.invitations.index');
        Route::get('tournaments/{tournament}/categories/{categoryId}/invite-partner', [\App\Http\Controllers\Player\InvitationController::class, 'search'])
            ->name('player.invitations.search');
    });
    
    // Manager Routes
    Route::middleware(['manager', 'verified'])->prefix('manager')->name('manager.')->group(function () {
        Route::get('/tournaments', [\App\Http\Controllers\Manager\TournamentController::class, 'index'])
            ->name('tournaments');
        Route::get('/tournaments/create', [\App\Http\Controllers\Manager\TournamentController::class, 'create'])
            ->name('tournaments.create');
        
        Route::get('/matches', [\App\Http\Controllers\Manager\MatchController::class, 'index'])
            ->name('matches');
        Route::get('/club', [\App\Http\Controllers\Manager\ClubController::class, 'index'])
            ->name('club');
        Route::get('/clubs', [\App\Http\Controllers\Manager\ClubController::class, 'allClubs'])
            ->name('clubs');
        Route::get('/clubs/{club}', [\App\Http\Controllers\Manager\ClubController::class, 'showClub'])
            ->name('clubs.show');
        Route::get('/club/edit', [\App\Http\Controllers\Manager\ClubController::class, 'edit'])
            ->name('club.edit');
        Route::put('/club', [\App\Http\Controllers\Manager\ClubController::class, 'update'])
            ->name('club.update');
        Route::post('/club/invite', [\App\Http\Controllers\Manager\ClubController::class, 'invitePlayer'])
            ->name('club.invite');
        Route::delete('/club/players/{clubPlayer}', [\App\Http\Controllers\Manager\ClubController::class, 'removePlayer'])
            ->name('club.remove-player');
        Route::post('/club/players/{clubPlayer}/assign-provisional', [\App\Http\Controllers\Manager\ClubController::class, 'assignProvisionalSkillLevel'])
            ->name('club.assign-provisional');
        Route::post('/club/players/{clubPlayer}/update-skill-level', [\App\Http\Controllers\Manager\ClubController::class, 'updateSkillLevel'])
            ->name('club.update-skill-level');
        
        Route::get('/players/{player}/biodata', [\App\Http\Controllers\Manager\ClubController::class, 'getPlayerBiodata'])
            ->name('players.biodata');
        
        Route::get('/players', [\App\Http\Controllers\Manager\PlayerController::class, 'index'])
            ->name('players');
        
        Route::get('/profile', [\App\Http\Controllers\Manager\ProfileController::class, 'index'])
            ->name('profile');
        Route::put('/profile', [\App\Http\Controllers\Manager\ProfileController::class, 'update'])
            ->name('profile.update');
        
        // Account Settings
        Route::put('/account/email', [\App\Http\Controllers\Manager\AccountSettingsController::class, 'updateEmail'])
            ->name('account.update-email');
        Route::put('/account/password', [\App\Http\Controllers\Manager\AccountSettingsController::class, 'updatePassword'])
            ->name('account.update-password');
        
        Route::get('/ranking', function () {
            $club = \App\Models\Club::where('manager_id', auth()->id())->first();
            $category = request()->get('category', 'All');
            $division = request()->get('division', 'All'); // 'Junior', 'Senior', 'Open', or 'All'
            
            // Map category name to ELO category code
            $categoryMap = [
                "Men's Singles" => 'MS',
                "Women's Singles" => 'WS',
                "Men's Doubles" => 'MD',
                "Women's Doubles" => 'WD',
                "Mixed Doubles" => 'XD',
            ];
            
            $categories = [
                'All',
                "Men's Singles",
                "Women's Singles",
                "Men's Doubles",
                "Women's Doubles",
                "Mixed Doubles"
            ];
            
            $divisions = [
                'All' => 'All Divisions',
                'Junior' => 'Junior (Under 18)',
                'Senior' => 'Senior (18+)',
                'Open' => 'Open (All Ages)',
            ];
            
            $rankingService = app(\App\Services\RankingService::class);
            
            // Convert 'All' division to null for service call
            $divisionFilter = ($division === 'All' || $division === '') ? null : $division;
            
            if ($category === 'All') {
                // Get all players showing their HIGHEST ELO across all categories
                $rankingsData = $rankingService->getAllRankingsHighestElo($divisionFilter);
            } else {
                $eloCategory = $categoryMap[$category] ?? 'MS';
                $rankingsData = $rankingService->getAllRankings($eloCategory, $divisionFilter);
                
                // Map ELO category code to category name patterns for database queries
                $categoryNamePatterns = [
                    'MS' => ['%men%singles%', '%mens%singles%'],
                    'WS' => ['%women%singles%', '%womens%singles%'],
                    'MD' => ['%men%doubles%', '%mens%doubles%'],
                    'WD' => ['%women%doubles%', '%womens%doubles%'],
                    'XD' => ['%mixed%'],
                ];
                $patterns = $categoryNamePatterns[$eloCategory] ?? ['%' . $eloCategory . '%'];
                
                // Filter: only show players who have actually played in this category
                // Having an ELO rating in this category already means they've played matches
                // But we also check registrations to be thorough
                $playerIds = collect($rankingsData)->pluck('player_id')->toArray();
                
                // Get players who registered for this category with matching age requirements
                $playedInCategoryQuery = \App\Models\TournamentRegistration::whereIn('player_id', $playerIds)
                    ->whereHas('category', function($query) use ($patterns, $divisionFilter) {
                        $query->where(function($q) use ($patterns) {
                            foreach ($patterns as $pattern) {
                                $q->orWhere('name', 'LIKE', $pattern);
                            }
                        });
                        
                        // Filter by age division if specified
                        if ($divisionFilter && in_array($divisionFilter, ['Junior', 'Senior'])) {
                            if ($divisionFilter === 'Junior') {
                                // Junior: max_age = 17 (Under 18)
                                $q->where('max_age', 17)->whereNull('min_age');
                            } elseif ($divisionFilter === 'Senior') {
                                // Senior: min_age = 18 (18+)
                                $q->where('min_age', 18)->whereNull('max_age');
                            }
                        } elseif ($divisionFilter === 'Open') {
                            // Open: both min_age and max_age are null
                            $q->whereNull('min_age')->whereNull('max_age');
                        }
                    })
                    ->where('status', 'approved');
                
                $playedInCategory = $playedInCategoryQuery->pluck('player_id')->unique()->toArray();
                
                // Get players who played matches in this category with matching age requirements
                $playedInMatchesQuery = \App\Models\TournamentMatch::where(function($query) use ($playerIds) {
                        $query->whereIn('player1_id', $playerIds)
                              ->orWhereIn('player2_id', $playerIds)
                              ->orWhereIn('player1_partner_id', $playerIds)
                              ->orWhereIn('player2_partner_id', $playerIds);
                    })
                    ->whereHas('category', function($query) use ($patterns, $divisionFilter) {
                        $query->where(function($q) use ($patterns) {
                            foreach ($patterns as $pattern) {
                                $q->orWhere('name', 'LIKE', $pattern);
                            }
                        });
                        
                        // Filter by age division if specified
                        if ($divisionFilter && in_array($divisionFilter, ['Junior', 'Senior'])) {
                            if ($divisionFilter === 'Junior') {
                                // Junior: max_age = 17 (Under 18)
                                $q->where('max_age', 17)->whereNull('min_age');
                            } elseif ($divisionFilter === 'Senior') {
                                // Senior: min_age = 18 (18+)
                                $q->where('min_age', 18)->whereNull('max_age');
                            }
                        } elseif ($divisionFilter === 'Open') {
                            // Open: both min_age and max_age are null
                            $q->whereNull('min_age')->whereNull('max_age');
                        }
                    });
                
                $playedInMatches = $playedInMatchesQuery->get()
                    ->flatMap(function($match) {
                        return array_filter([
                            $match->player1_id,
                            $match->player2_id,
                            $match->player1_partner_id,
                            $match->player2_partner_id
                        ]);
                    })
                    ->unique()
                    ->toArray();
                
                // Combine both - if player has ELO rating AND has played in category, show them
                $playedPlayerIds = array_unique(array_merge($playedInCategory, $playedInMatches));
                
                // Filter rankings to only include players who have actually played
                $rankingsData = array_filter($rankingsData, function($data) use ($playedPlayerIds) {
                    // If player has matches_played > 0, they've definitely played
                    // OR if they're in the played list
                    return $data['matches_played'] > 0 || in_array($data['player_id'], $playedPlayerIds);
                });
                $rankingsData = array_values($rankingsData);
            }
            
            // Get win counts for players with matches
            // For doubles, both players on the winning team should get a win
            $playerIds = collect($rankingsData)->pluck('player_id')->toArray();
            
            // Get all match results for these players
            $matchResults = \App\Models\MatchResult::with('match')
                ->whereHas('match', function($query) use ($playerIds) {
                    $query->where(function($q) use ($playerIds) {
                        $q->whereIn('player1_id', $playerIds)
                          ->orWhereIn('player2_id', $playerIds)
                          ->orWhereIn('player1_partner_id', $playerIds)
                          ->orWhereIn('player2_partner_id', $playerIds);
                    });
                })
                ->get();
            
            // Count wins for each player (accounting for doubles partners)
            $winCounts = [];
            foreach ($matchResults as $result) {
                $match = $result->match;
                if (!$match || !$match->winner_id) {
                    continue;
                }
                
                // Check if this is a doubles match
                $isDoubles = $match->player1_partner_id || $match->player2_partner_id;
                
                if ($isDoubles && $match->winner_partner_id) {
                    // Doubles: both players on winning team get a win
                    if (in_array($match->winner_id, $playerIds)) {
                        $winCounts[$match->winner_id] = ($winCounts[$match->winner_id] ?? 0) + 1;
                    }
                    if (in_array($match->winner_partner_id, $playerIds)) {
                        $winCounts[$match->winner_partner_id] = ($winCounts[$match->winner_partner_id] ?? 0) + 1;
                    }
                } else {
                    // Singles: only winner gets a win
                    if (in_array($match->winner_id, $playerIds)) {
                        $winCounts[$match->winner_id] = ($winCounts[$match->winner_id] ?? 0) + 1;
                    }
                }
            }
            
            // Count losses for each player (accounting for doubles partners)
            $lossCounts = [];
            foreach ($matchResults as $result) {
                $match = $result->match;
                if (!$match || !$match->winner_id) {
                    continue;
                }
                
                // Check if this is a doubles match
                $isDoubles = $match->player1_partner_id || $match->player2_partner_id;
                
                // Determine losing team
                $loserId = null;
                $loserPartnerId = null;
                if ($match->winner_id === $match->player1_id) {
                    $loserId = $match->player2_id;
                    $loserPartnerId = $match->player2_partner_id;
                } else {
                    $loserId = $match->player1_id;
                    $loserPartnerId = $match->player1_partner_id;
                }
                
                if ($isDoubles && $loserPartnerId) {
                    // Doubles: both players on losing team get a loss
                    if (in_array($loserId, $playerIds)) {
                        $lossCounts[$loserId] = ($lossCounts[$loserId] ?? 0) + 1;
                    }
                    if (in_array($loserPartnerId, $playerIds)) {
                        $lossCounts[$loserPartnerId] = ($lossCounts[$loserPartnerId] ?? 0) + 1;
                    }
                } else {
                    // Singles: only loser gets a loss
                    if (in_array($loserId, $playerIds)) {
                        $lossCounts[$loserId] = ($lossCounts[$loserId] ?? 0) + 1;
                    }
                }
            }
            
            // Count actual matches played for each player from TournamentMatch
            $matchesPlayedCounts = [];
            foreach ($matchResults as $result) {
                $match = $result->match;
                if (!$match) continue;
                
                $isDoubles = $match->player1_partner_id || $match->player2_partner_id;
                
                // Count match for player1
                if (in_array($match->player1_id, $playerIds)) {
                    $matchesPlayedCounts[$match->player1_id] = ($matchesPlayedCounts[$match->player1_id] ?? 0) + 1;
                }
                
                // Count match for player2
                if (in_array($match->player2_id, $playerIds)) {
                    $matchesPlayedCounts[$match->player2_id] = ($matchesPlayedCounts[$match->player2_id] ?? 0) + 1;
                }
                
                // Count match for doubles partners
                if ($isDoubles) {
                    if ($match->player1_partner_id && in_array($match->player1_partner_id, $playerIds)) {
                        $matchesPlayedCounts[$match->player1_partner_id] = ($matchesPlayedCounts[$match->player1_partner_id] ?? 0) + 1;
                    }
                    if ($match->player2_partner_id && in_array($match->player2_partner_id, $playerIds)) {
                        $matchesPlayedCounts[$match->player2_partner_id] = ($matchesPlayedCounts[$match->player2_partner_id] ?? 0) + 1;
                    }
                }
            }
            
            // Build final rankings with rank numbers
            // Only assign numeric ranks to players with official rankings (matches_played > 0)
            // Rankings are sorted by ELO rating (highest first)
            $officialRankCounter = 0;
            $rankings = collect($rankingsData)->map(function ($data) use ($winCounts, $lossCounts, $matchesPlayedCounts, &$officialRankCounter) {
                $wins = $winCounts[$data['player_id']] ?? 0;
                $losses = $lossCounts[$data['player_id']] ?? 0;
                // Use actual match count from TournamentMatch, fallback to wins + losses if available
                $actualMatchesPlayed = $matchesPlayedCounts[$data['player_id']] ?? ($wins + $losses);
                
                // Only assign numeric rank if player has official ranking (played at least 1 match)
                $rank = null;
                if ($data['has_official_ranking'] ?? ($actualMatchesPlayed > 0)) {
                    $officialRankCounter++;
                    $rank = $officialRankCounter;
                }
                
                return (object)[
                    'rank' => $rank, // null for provisional players
                    'player_id' => $data['player_id'], // Add player_id for navigation
                    'player' => $data['player'],
                    'club' => $data['club'],
                    'matches_played' => $actualMatchesPlayed,
                    'wins' => $wins,
                    'losses' => $losses,
                    'win_rate' => $actualMatchesPlayed > 0 
                        ? round(($wins / $actualMatchesPlayed) * 100, 1) 
                        : 0,
                    'current_rating' => $data['current_rating'],
                    'peak_rating' => $data['peak_rating'],
                    'is_provisional' => $data['is_provisional'] ?? false,
                    'has_official_ranking' => $data['has_official_ranking'] ?? ($actualMatchesPlayed > 0),
                ];
            })->values();
            
            // Ensure proper sorting: official rankings first (by rating descending), then provisional
            $rankings = $rankings->sort(function($a, $b) {
                // If both have official rankings, sort by rating descending (highest first)
                if (($a->has_official_ranking ?? false) && ($b->has_official_ranking ?? false)) {
                    return $b->current_rating <=> $a->current_rating;
                }
                // If only a has official ranking, a comes first
                if ($a->has_official_ranking ?? false) {
                    return -1;
                }
                // If only b has official ranking, b comes first
                if ($b->has_official_ranking ?? false) {
                    return 1;
                }
                // Both are provisional (N/A), sort by rating descending
                return $b->current_rating <=> $a->current_rating;
            })->values();
            
            // Now assign ranks based on sorted order (highest rating = rank 1)
            $officialRankCounter = 0;
            $rankings = $rankings->map(function($ranking) use (&$officialRankCounter) {
                if ($ranking->has_official_ranking ?? false) {
                    $officialRankCounter++;
                    $ranking->rank = $officialRankCounter;
                } else {
                    $ranking->rank = null;
                }
                return $ranking;
            });
            
            $clubs = \App\Models\Club::orderBy('name')->get();
            
            // Return response with cache-busting headers to ensure fresh ELO data
            return response()
                ->view('manager.ranking', compact('rankings', 'club', 'clubs', 'categories', 'category', 'divisions', 'division'))
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        })->name('ranking');
    });
    
    // Player Profile Routes (accessible even if biodata is incomplete)
    Route::middleware(['player', 'verified'])->group(function () {
        Route::get('/profile', [\App\Http\Controllers\Player\ProfileController::class, 'show'])
            ->name('profile.index');
        Route::get('/player/profile', fn () => redirect()->route('profile.index'))
            ->name('player.profile.index.redirect');
        Route::get('/profile/edit', [\App\Http\Controllers\Player\ProfileController::class, 'edit'])
            ->name('profile.edit');
        Route::put('/profile', [\App\Http\Controllers\Player\ProfileController::class, 'update'])
            ->name('profile.update');
        
        // Account Settings
        Route::put('/account/email', [\App\Http\Controllers\Player\AccountSettingsController::class, 'updateEmail'])
            ->name('player.account.update-email');
        Route::put('/account/password', [\App\Http\Controllers\Player\AccountSettingsController::class, 'updatePassword'])
            ->name('player.account.update-password');
    });
    
    // Player Routes (protected with player middleware and biodata completion)
    Route::middleware(['player', 'verified', 'biodata.completed'])->group(function () {
        // Tournaments
        Route::get('/tournaments', [\App\Http\Controllers\Player\TournamentController::class, 'index'])
            ->name('tournaments.index');
        // Backward-compatible aliases to prevent 404s when users hit prefixed URLs
        Route::get('/player/tournaments', fn () => redirect()->route('tournaments.index'))
            ->name('player.tournaments.index.redirect');
        
        Route::get('/tournaments/{tournament}', [\App\Http\Controllers\Player\TournamentController::class, 'show'])
            ->name('tournaments.show');
        
        // Ranking
        Route::get('/ranking', [\App\Http\Controllers\Player\RankingController::class, 'index'])
            ->name('ranking.index');
        Route::get('/player/ranking', fn () => redirect()->route('ranking.index'))
            ->name('player.ranking.index.redirect');
        
        // Clubs
        Route::get('/clubs', [\App\Http\Controllers\Player\ClubController::class, 'index'])
            ->name('clubs.index');
        Route::get('/player/clubs', fn () => redirect()->route('clubs.index'))
            ->name('player.clubs.index.redirect');
        
        Route::get('/clubs/{club}', [\App\Http\Controllers\Player\ClubController::class, 'show'])
            ->name('clubs.show');
        
        Route::post('/clubs/{club}/join', [\App\Http\Controllers\Player\ClubController::class, 'join'])
            ->name('clubs.join');
        
        // Club Invitation Management
        Route::post('/club-invitations/{clubPlayer}/accept', [\App\Http\Controllers\Player\ClubController::class, 'acceptInvitation'])
            ->name('player.club.invitations.accept');
        
        Route::post('/club-invitations/{clubPlayer}/reject', [\App\Http\Controllers\Player\ClubController::class, 'rejectInvitation'])
            ->name('player.club.invitations.reject');
        
        // Players (view all players in the system)
        Route::get('/players', [\App\Http\Controllers\Player\PlayerController::class, 'index'])
            ->name('players.index');
        Route::get('/player/players', fn () => redirect()->route('players.index'))
            ->name('player.players.index.redirect');
        
        Route::get('/player/matches', [\App\Http\Controllers\Player\MatchController::class, 'index'])
            ->name('matches.index');
        
        Route::get('/player/statistics/export/{format?}', [\App\Http\Controllers\Player\StatisticsExportController::class, 'exportPlayerStatistics'])
            ->name('player.statistics.export');
    });
    
    // Shared Routes (accessible by both managers and players)
    Route::middleware('auth')->group(function () {
        // View player biodata (managers can view, players can view their own)
        Route::get('/players/{user}', [\App\Http\Controllers\Player\ProfileController::class, 'showOther'])
            ->name('players.show');
        
        // Notifications
        Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])
            ->name('notifications.index');
        Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])
            ->name('notifications.markAsRead');
        Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])
            ->name('notifications.markAllAsRead');
        
        // Withdrawal
        Route::get('/withdrawal', function () {
            return view('withdrawal.index');
        })->name('withdrawal.index');
        
        Route::post('/withdrawal', [\App\Http\Controllers\Player\WithdrawalController::class, 'store'])
            ->middleware('player')
            ->name('withdrawal.store');
        
        // Payment
        Route::get('/payment', function () {
            return view('payment.index');
        })->name('payment.index');
        
        // Restricted Access
        Route::get('/restricted', function () {
            return view('restricted');
        })->name('restricted');
    });
}); // Close auth middleware group

require __DIR__.'/auth.php';
