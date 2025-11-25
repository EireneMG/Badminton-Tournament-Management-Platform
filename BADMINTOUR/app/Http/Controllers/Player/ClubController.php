<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\ClubPlayer;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClubController extends Controller
{
    public function index(): View
    {
        $player = auth()->user();
        
        $myClub = null;
        $myMembership = $player->approvedClubMembership;
        if ($myMembership) {
            $myClub = $myMembership->club;
        }
        
        $pendingRequests = ClubPlayer::where('player_id', $player->id)
            ->where('status', 'pending')
            ->with('club')
            ->get();
        
        $allClubs = Club::withCount('approvedPlayers')
            ->where('active', true)
            ->get();
        
        return view('clubs.index', compact('myClub', 'allClubs', 'pendingRequests'));
    }
    
    public function show(Club $club): View
    {
        $player = auth()->user();
        
        $club->load(['approvedPlayers', 'tournaments' => function($query) {
            $query->where('start_date', '>=', now())->orderBy('start_date', 'asc');
        }]);
        
        $memberCount = $club->approvedPlayers->count();
        
        $myMembership = ClubPlayer::where('club_id', $club->id)
            ->where('player_id', $player->id)
            ->first();
        
        $canJoin = !$myMembership && !$player->approvedClubMembership;
        $isPending = $myMembership && $myMembership->status === 'pending';
        $isApproved = $myMembership && $myMembership->status === 'approved';
        
        return view('clubs.show', compact('club', 'memberCount', 'canJoin', 'isPending', 'isApproved'));
    }
}
