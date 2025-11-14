<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('landing');

// Registration flow
Route::middleware('guest')->group(function () {
    Route::get('/register', function () {
        return view('auth.register-select');
    })->name('register');
    
    Route::get('/register/player', function () {
        return view('auth.register-player');
    })->name('register.player');
    
    Route::get('/register/manager', function () {
        return view('auth.register-manager');
    })->name('register.manager');
    
    // Manager login page
    Route::get('/login/manager', function () {
        return view('auth.login-manager');
    })->name('login.manager');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/player/dashboard', function () {
    return view('dashboard-player');
})->middleware(['auth', 'verified', 'player'])->name('player.dashboard');

Route::get('/manager/dashboard', function () {
    return view('manager.dashboard');
})->middleware(['auth', 'verified', 'manager'])->name('manager.dashboard');

Route::middleware('auth')->group(function () {
    // Manager Onboarding Routes
    Route::get('/manager/verify-id', function () {
        return view('manager.verify-id');
    })->middleware('manager')->name('manager.verify-id');
    
    Route::post('/manager/verify-id', function () {
        return redirect()->route('manager.create-club');
    })->middleware('manager')->name('manager.verify-id.submit');
    
    Route::get('/manager/create-club', function () {
        return view('manager.create-club');
    })->middleware('manager')->name('manager.create-club');
    
    Route::post('/manager/create-club', function () {
        return redirect()->route('manager.dashboard');
    })->middleware('manager')->name('manager.create-club.submit');

    // Manager Routes
    Route::get('/manager/tournaments', function () {
        return view('manager.tournaments');
    })->middleware('manager')->name('manager.tournaments');
    
    Route::get('/manager/tournaments/create', function () {
        return view('manager.tournaments.create');
    })->middleware('manager')->name('manager.tournaments.create');
    
    Route::get('/manager/tournaments/generate', function () {
        return view('manager.tournaments.generate');
    })->middleware('manager')->name('manager.tournaments.generate');
    
    Route::get('/manager/matches', function () {
        return view('manager.matches');
    })->middleware('manager')->name('manager.matches');
    
    Route::get('/manager/club', function () {
        return view('manager.club');
    })->middleware('manager')->name('manager.club');
    
    Route::get('/manager/ranking', function () {
        return view('manager.ranking');
    })->middleware('manager')->name('manager.ranking');
    
    Route::get('/manager/players', function () {
        return view('manager.players');
    })->middleware('manager')->name('manager.players');
    
    Route::get('/manager/profile', function () {
        return view('manager.profile');
    })->middleware('manager')->name('manager.profile');

    // Tournaments
    Route::get('/tournaments', function () {
        return view('tournaments.index');
    })->name('tournaments.index');
    
    Route::get('/tournaments/{id}', function () {
        return view('tournaments.show');
    })->name('tournaments.show');
    
    // Ranking
    Route::get('/ranking', function () {
        return view('ranking.index');
    })->name('ranking.index');
    
    // Clubs
    Route::get('/clubs', function () {
        return view('clubs.index');
    })->name('clubs.index');
    
    Route::get('/clubs/{id}', function () {
        return view('clubs.show');
    })->name('clubs.show');
    
    // Players
    Route::get('/players', function () {
        return view('players.index');
    })->name('players.index');
    
    Route::get('/players/{id}', function () {
        return view('players.show');
    })->name('players.show');
    
    // Profile
    Route::get('/profile', function () {
        return view('profile.index');
    })->name('profile.index');
    
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