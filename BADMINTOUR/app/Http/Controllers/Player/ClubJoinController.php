<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Http\Requests\Player\JoinClubRequest;
use App\Models\Club;
use App\Models\ClubPlayer;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;

class ClubJoinController extends Controller
{
    /**
     * Request to join a club.
     */
    public function store(JoinClubRequest $request): RedirectResponse
    {
        $player = $request->user();
        $club = Club::findOrFail($request->club_id);

        // Check if player already has an approved club
        $approvedMembership = $player->clubMemberships()
            ->where('status', 'approved')
            ->first();

        if ($approvedMembership) {
            return back()->with('error', 'You are already a member of a club.');
        }

        // Check if player already has a pending request to this club
        $existingRequest = ClubPlayer::where('club_id', $club->id)
            ->where('player_id', $player->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'You already have a pending or approved request to this club.');
        }

        // Create join request
        ClubPlayer::create([
            'club_id' => $club->id,
            'player_id' => $player->id,
            'status' => 'pending',
            'request_type' => 'join_request',
        ]);

        // Notify club manager
        Notification::create([
            'user_id' => $club->manager_id,
            'type' => 'club_join_request',
            'title' => 'New Club Join Request',
            'message' => "{$player->name} has requested to join {$club->name}.",
            'data' => ['player_id' => $player->id, 'club_id' => $club->id],
            'action_url' => route('manager.club'),
        ]);

        return back()->with('success', 'Club join request submitted successfully!');
    }

    /**
     * Approve a club join request (Manager only).
     */
    public function approve($clubPlayerId): RedirectResponse
    {
        $clubPlayer = ClubPlayer::with('club')->findOrFail($clubPlayerId);
        
        // Authorization: Verify the manager owns this club
        if ($clubPlayer->club->manager_id !== request()->user()->id) {
            abort(403, 'Unauthorized. You can only approve requests for your own club.');
        }
        
        // Business Rule: Check if player already has an approved club membership
        $existingApprovedMembership = ClubPlayer::where('player_id', $clubPlayer->player_id)
            ->where('status', 'approved')
            ->where('id', '!=', $clubPlayer->id)
            ->exists();
        
        if ($existingApprovedMembership) {
            return back()->with('error', 'This player is already a member of another club. They must leave their current club before joining yours.');
        }
        
        $clubPlayer->update([
            'status' => 'approved',
            'provisional_elo' => request('provisional_elo', 1200),
            'skill_level' => request('skill_level'),
        ]);

        // Notify player
        Notification::create([
            'user_id' => $clubPlayer->player_id,
            'type' => 'club_join_approved',
            'title' => 'Club Join Request Approved',
            'message' => "Your request to join {$clubPlayer->club->name} has been approved!",
            'data' => ['club_id' => $clubPlayer->club_id],
            'action_url' => route('clubs.show', $clubPlayer->club_id),
        ]);

        return back()->with('success', 'Player approved successfully!');
    }
}
