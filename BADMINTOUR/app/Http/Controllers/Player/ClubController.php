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
    
    public function join(Request $request, Club $club): RedirectResponse
    {
        $player = auth()->user();
        
        $existingMembership = ClubPlayer::where('club_id', $club->id)
            ->where('player_id', $player->id)
            ->first();
        
        if ($existingMembership) {
            return back()->with('error', 'You have already requested to join this club.');
        }
        
        if ($player->approvedClubMembership) {
            return back()->with('error', 'You are already a member of another club. You can only be in one club at a time.');
        }
        
        ClubPlayer::create([
            'club_id' => $club->id,
            'player_id' => $player->id,
            'status' => 'pending',
        ]);
        
        Notification::create([
            'user_id' => $club->manager_id,
            'type' => 'club_join_request',
            'title' => 'New Club Join Request',
            'message' => $player->first_name . ' ' . $player->last_name . ' has requested to join ' . $club->name,
            'data' => [
                'club_id' => $club->id,
                'player_id' => $player->id,
            ],
            'action_url' => route('manager.club'),
        ]);
        
        return back()->with('success', 'Join request sent successfully! The club manager will review your request.');
    }
}
