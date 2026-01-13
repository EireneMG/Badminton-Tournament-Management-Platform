<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Http\Requests\Player\JoinClubRequest;
use App\Models\Club;
use App\Models\ClubPlayer;
use App\Models\Notification;
use App\Models\User;
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
            'message' => "{$player->first_name} {$player->last_name} has requested to join {$club->name}.",
            'data' => ['player_id' => $player->id, 'club_id' => $club->id],
            'action_url' => route('manager.club'),
        ]);

        return back()->with('success', 'Club join request submitted successfully!');
    }

    /**
     * Approve a club join request (Manager only).
     */
    public function approve(ClubPlayer $clubPlayer): RedirectResponse
    {
        $clubPlayer->load('club');
        
        // Authorization: Verify the manager owns this club
        if (!$clubPlayer->club || $clubPlayer->club->manager_id !== request()->user()->id) {
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
        
        // Get provisional skill level from request
        $provisionalSkillLevel = request('provisional_skill_level');
        
        if (!$provisionalSkillLevel || !in_array($provisionalSkillLevel, ['A', 'B', 'C', 'D'])) {
            return back()->with('error', 'Please assign a provisional skill level (A, B, C, or D).');
        }
        
        // Convert provisional skill level to ELO rating
        $provisionalElo = \App\Helpers\SkillLevelHelper::convertSkillLevelToElo($provisionalSkillLevel);
        
        // Create official ELO rating from provisional
        $this->createEloFromProvisional($clubPlayer->player, $provisionalElo);
        
        // Update club player record
        $clubPlayer->update([
            'status' => 'approved',
            'provisional_elo' => $provisionalElo,
            'skill_level' => $provisionalSkillLevel,
            'is_provisional' => true, // Mark as provisional - cannot be changed by manager
        ]);

        // Notify player
        $notification = Notification::create([
            'user_id' => $clubPlayer->player_id,
            'type' => 'club_join_approved',
            'title' => 'Club Join Request Approved',
            'message' => "Your request to join " . ($clubPlayer->club?->name ?? 'the club') . " has been approved! Your provisional skill level ({$provisionalSkillLevel}) has been set and converted to an initial ELO rating of {$provisionalElo}.",
            'data' => ['club_id' => $clubPlayer->club_id],
            'action_url' => route('clubs.show', $clubPlayer->club_id),
        ]);

        // Send email notification
        app(\App\Services\EmailService::class)->sendNotificationEmail($notification);

        return back()->with('success', 'Player approved successfully! Provisional skill level assigned and ELO rating created.');
    }
    
    
    /**
     * Create official ELO rating from provisional skill level
     * Creates ELO records for all gender-appropriate categories
     */
    protected function createEloFromProvisional(User $player, int $eloRating): void
    {
        // Determine categories based on player gender
        // Male players: MS, MD, XD
        // Female players: WS, WD, XD
        $isMale = $player->gender === 'Male';
        $categories = $isMale 
            ? ['MS', 'MD', 'XD']  // Male categories
            : ['WS', 'WD', 'XD']; // Female categories
        
        // Create or update ELO ratings for all gender-appropriate categories
        foreach ($categories as $category) {
            \App\Models\EloRating::updateOrCreate(
                [
                    'player_id' => $player->id,
                    'category' => $category,
                ],
                [
                    'current_rating' => $eloRating,
                    'peak_rating' => $eloRating,
                    'matches_played' => 0,
                ]
            );
            
            // Create ranking history entry for each category
            \App\Models\RankingHistory::create([
                'player_id' => $player->id,
                'category' => $category,
                'rating' => $eloRating,
                'previous_rating' => null,
                'change' => 0,
                'recorded_at' => now(),
            ]);
        }
    }

    /**
     * Reject a club join request (Manager only).
     */
    public function reject(ClubPlayer $clubPlayer): RedirectResponse
    {
        $clubPlayer->load('club');
        
        // Authorization: Verify the manager owns this club
        if (!$clubPlayer->club || $clubPlayer->club->manager_id !== request()->user()->id) {
            abort(403, 'Unauthorized. You can only reject requests for your own club.');
        }
        
        $clubPlayer->update(['status' => 'rejected']);

        // Notify player
        $notification = Notification::create([
            'user_id' => $clubPlayer->player_id,
            'type' => 'club_join_rejected',
            'title' => 'Club Join Request Rejected',
            'message' => "Your request to join " . ($clubPlayer->club?->name ?? 'the club') . " was not approved.",
            'data' => ['club_id' => $clubPlayer->club_id],
            'action_url' => route('player.dashboard'),
        ]);

        // Send email notification
        app(\App\Services\EmailService::class)->sendNotificationEmail($notification);

        return back()->with('success', 'Player request rejected.');
    }
}
