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
}
