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

    
});