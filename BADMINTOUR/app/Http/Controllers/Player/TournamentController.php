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
        
        $canWithdraw = $playerRegistration 
            && $playerRegistration->status === 'approved'
            && Carbon::now()->isBefore(Carbon::parse($tournament->start_date)->subDays(3));
        
        return view('tournaments.show', compact(
            'tournament',
            'playerRegistration',
            'isEligible',
            'canRegister',
            'canWithdraw'
        ));
    }
}
