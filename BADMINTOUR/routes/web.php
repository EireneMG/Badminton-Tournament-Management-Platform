<?php

use App\Http\Controllers\ProfileController;
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
    Route::get('/login/manager', function () {
        return view('auth.login-manager');
    })->name('login.manager');
});

// Generic dashboard route - redirects to role-specific dashboard
Route::get('/dashboard', function () {
    $dashboardRoute = auth()->user()->getDashboardRoute();
    return redirect()->route($dashboardRoute);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/player/dashboard', [\App\Http\Controllers\Player\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'player'])->name('player.dashboard');

Route::get('/manager/dashboard', function () {
    return view('manager.dashboard');
})->middleware(['auth', 'verified', 'manager'])->name('manager.dashboard');

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
    
    // Manager Tournament System Routes
    Route::middleware(['manager', 'verified', 'verifiedId', 'hasClub'])->prefix('manager')->group(function () {
        // Tournament CRUD
        Route::resource('tournaments', \App\Http\Controllers\Manager\TournamentController::class);
        Route::post('tournaments/{tournament}/publish', [\App\Http\Controllers\Manager\TournamentController::class, 'publish'])
            ->name('manager.tournaments.publish');
        Route::post('tournaments/{tournament}/generate-matches', [\App\Http\Controllers\Manager\TournamentController::class, 'generateMatches'])
            ->name('manager.tournaments.generate-matches');
        
        // Tournament Registration Management
        Route::post('registrations/{registration}/update-status', [\App\Http\Controllers\Manager\TournamentRegistrationController::class, 'updateStatus'])
            ->name('manager.registrations.update-status');
        Route::post('registrations/bulk-update-status', [\App\Http\Controllers\Manager\TournamentRegistrationController::class, 'bulkUpdateStatus'])
            ->name('manager.registrations.bulk-update-status');
        
        // Match Management
        Route::post('matches/{match}/update-score', [\App\Http\Controllers\Manager\MatchController::class, 'updateScore'])
            ->name('manager.matches.update-score');
        Route::post('matches/{match}/reschedule', [\App\Http\Controllers\Manager\MatchController::class, 'reschedule'])
            ->name('manager.matches.reschedule');
    });
    
    // Player Tournament System Routes
    Route::middleware(['player', 'verified'])->prefix('player')->group(function () {
        // Tournament Registration
        Route::post('tournaments/register', [\App\Http\Controllers\Player\TournamentRegistrationController::class, 'register'])
            ->name('player.tournaments.register');
        Route::delete('registrations/{registration}/cancel', [\App\Http\Controllers\Player\TournamentRegistrationController::class, 'cancel'])
            ->name('player.registrations.cancel');
        Route::post('registrations/{registration}/withdraw', [\App\Http\Controllers\Player\TournamentRegistrationController::class, 'withdraw'])
            ->name('player.registrations.withdraw');
        
        // Player-specific tournament views
        Route::get('tournaments/{tournament}', [\App\Http\Controllers\Player\TournamentController::class, 'show'])
            ->name('player.tournaments.show');
        
        Route::get('matches/{match}', function ($id) {
            return view('matches.show', ['match_id' => $id]);
        })->name('player.matches.show');
    });
    
    // Manager Routes
    Route::middleware(['manager', 'verified'])->prefix('manager')->group(function () {
        Route::get('/tournaments', [\App\Http\Controllers\Manager\TournamentController::class, 'index'])
            ->name('manager.tournaments');
        Route::get('/tournaments/create', [\App\Http\Controllers\Manager\TournamentController::class, 'create'])
            ->name('manager.tournaments.create');
        Route::get('/tournaments/generate', function () {
            return view('manager.tournaments.generate');
        })->name('manager.tournaments.generate');
        
        Route::get('/matches', [\App\Http\Controllers\Manager\MatchController::class, 'index'])
            ->name('manager.matches');
        Route::get('/club', [\App\Http\Controllers\Manager\ClubController::class, 'index'])
            ->name('manager.club');
        Route::get('/ranking', function () {
            $club = \App\Models\Club::where('manager_id', auth()->id())->first();
            $rankings = \App\Models\EloRating::with('player')
                ->orderBy('current_rating', 'desc')
                ->paginate(50);
            return view('manager.ranking', compact('rankings', 'club'));
        })->name('manager.ranking');
    });
    
    Route::get('/manager/players', function () {
        return view('manager.players');
    })->middleware('manager')->name('manager.players');
    
    Route::get('/manager/profile', function () {
        return view('manager.profile');
    })->middleware('manager')->name('manager.profile');
    
    // Tournaments
    Route::get('/tournaments', [\App\Http\Controllers\Player\TournamentController::class, 'index'])
        ->name('tournaments.index');
    
    Route::get('/tournaments/{tournament}', [\App\Http\Controllers\Player\TournamentController::class, 'show'])
        ->name('tournaments.show');
    
    // Ranking
    Route::get('/ranking', [\App\Http\Controllers\Player\RankingController::class, 'index'])
        ->name('ranking.index');
    
    // Clubs
    Route::get('/clubs', [\App\Http\Controllers\Player\ClubController::class, 'index'])
        ->name('clubs.index');
    
    Route::get('/clubs/{club}', [\App\Http\Controllers\Player\ClubController::class, 'show'])
        ->name('clubs.show');
    
    Route::post('/clubs/{club}/join', [\App\Http\Controllers\Player\ClubController::class, 'join'])
        ->middleware('player')->name('clubs.join');
    
    // Players (this is for viewing other players - not implemented yet)
    Route::get('/players', function () {
        return view('players.index');
    })->name('players.index');
    
    Route::get('/players/{user}', [\App\Http\Controllers\Player\ProfileController::class, 'showOther'])
        ->name('players.show');
    
    // Player Profile (logged in player views their own profile)
    Route::get('/profile', [\App\Http\Controllers\Player\ProfileController::class, 'show'])
        ->middleware('player')->name('profile.index');
    
    // Matches
    Route::get('/matches', [\App\Http\Controllers\Player\MatchController::class, 'index'])
        ->middleware('player')->name('matches.index');
    
    // Notifications
    Route::get('/notifications', function () {
        return view('notifications.index');
    })->name('notifications.index');
    
    // Withdrawal
    Route::get('/withdrawal', function () {
        return view('withdrawal.index');
    })->name('withdrawal.index');
    
    Route::get('/withdrawal/status', function () {
        return view('withdrawal.status');
    })->name('withdrawal.status');
    
    // Payment
    Route::get('/payment', function () {
        return view('payment.index');
    })->name('payment.index');
    
    // Restricted Access
    Route::get('/restricted', function () {
        return view('restricted');
    })->name('restricted');
    
    // Original profile routes (Breeze)
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
