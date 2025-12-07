<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentRegistration;
use Illuminate\View\View;
use Carbon\Carbon;

class TournamentController extends Controller
{
    public function index(): View
    {
        $player = auth()->user();
        $now = Carbon::now();
        
        $ongoingTournaments = Tournament::where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->with(['club', 'categories'])
            ->get();
        
        $upcomingTournaments = Tournament::where('start_date', '>', $now)
            ->where('registration_deadline', '>', $now)
            ->with(['club', 'categories'])
            ->orderBy('start_date', 'asc')
            ->get();
        
        $completedTournaments = Tournament::where('end_date', '<', $now)
            ->with(['club', 'categories'])
            ->orderBy('end_date', 'desc')
            ->limit(10)
            ->get();
        
        $myTournaments = TournamentRegistration::where('player_id', $player->id)
            ->with(['tournament.club', 'category'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->pluck('tournament')
            ->unique('id');
        
        return view('tournaments.index', compact(
            'ongoingTournaments',
            'upcomingTournaments',
            'completedTournaments',
            'myTournaments'
        ));
    }
    
    public function show(Tournament $tournament): View
    {
        $player = auth()->user();
        
        $tournament->load([
            'club.manager',
            'categories.registrations',
            'categories' => function ($query) {
                $query->withCount(['registrations as approved_count' => function ($q) {
                    $q->where('status', 'approved');
                }]);
            }
        ]);
        
        $playerRegistration = TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('player_id', $player->id)
            ->first();
        
        $clubMembership = $player->approvedClubMembership;
        $isEligible = $clubMembership && $clubMembership->status === 'approved';
        
        $canRegister = !$playerRegistration 
            && $isEligible
            && Carbon::now()->isBefore($tournament->registration_deadline)
            && Carbon::now()->isBefore($tournament->start_date);
        
        // Check if player can withdraw (before withdrawal deadline and tournament hasn't started)
        $canWithdraw = false;
        if ($playerRegistration && in_array($playerRegistration->status, ['approved', 'paid', 'awaiting_payment'])) {
            // Check if withdrawal deadline has passed
            if ($tournament->withdrawal_deadline && Carbon::now()->isBefore($tournament->withdrawal_deadline)) {
                // Check if tournament hasn't started
                if (Carbon::now()->isBefore($tournament->start_date)) {
                    // Check if there's no pending withdrawal request
                    $hasPendingWithdrawal = \App\Models\WithdrawalRequest::where('tournament_registration_id', $playerRegistration->id)
                        ->where('status', 'pending')
                        ->exists();
                    $canWithdraw = !$hasPendingWithdrawal;
                }
            }
        }
        
        return view('tournaments.show', compact(
            'tournament',
            'playerRegistration',
            'isEligible',
            'canRegister',
            'canWithdraw'
        ));
    }
}
