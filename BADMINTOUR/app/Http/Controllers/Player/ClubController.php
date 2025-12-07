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
            // Load relationships for the club
            $myClub->load(['approvedPlayers', 'tournaments']);
        }
        
        $pendingRequests = ClubPlayer::where('player_id', $player->id)
            ->where('status', 'pending')
            ->where('request_type', 'join_request')
            ->with('club')
            ->get();
        
        // Get club invitations (status = 'invited', request_type = 'invitation')
        $clubInvitations = ClubPlayer::where('player_id', $player->id)
            ->where('status', 'invited')
            ->where('request_type', 'invitation')
            ->with('club')
            ->get();
        
        $allClubs = Club::withCount('approvedPlayers')
            ->where('active', true)
            ->get();
        
        return view('clubs.index', compact('myClub', 'allClubs', 'pendingRequests', 'clubInvitations'));
    }
    
    public function show(Club $club): View
    {
        $player = auth()->user();
        
        $club->load(['tournaments' => function($query) {
            $query->where('start_date', '>=', now())->orderBy('start_date', 'asc');
        }]);
        
        // Get approved players sorted by name (A-Z)
        $approvedPlayers = $club->approvedPlayers()
            ->orderByRaw('LOWER(first_name), LOWER(last_name)')
            ->get();
        
        $memberCount = $approvedPlayers->count();
        
        $myMembership = ClubPlayer::where('club_id', $club->id)
            ->where('player_id', $player->id)
            ->first();
        
        $canJoin = !$myMembership && !$player->approvedClubMembership;
        $isPending = $myMembership && $myMembership->status === 'pending';
        $isApproved = $myMembership && $myMembership->status === 'approved';
        
        return view('clubs.show', compact('club', 'approvedPlayers', 'memberCount', 'canJoin', 'isPending', 'isApproved'));
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
    
    /**
     * Accept a club invitation.
     */
    public function acceptInvitation(ClubPlayer $clubPlayer): RedirectResponse
    {
        $player = auth()->user();
        
        // Verify this invitation belongs to the current player
        if ($clubPlayer->player_id !== $player->id) {
            abort(403, 'Unauthorized. This invitation does not belong to you.');
        }
        
        // Verify this is an invitation (not a join request)
        if ($clubPlayer->status !== 'invited' || $clubPlayer->request_type !== 'invitation') {
            return back()->with('error', 'This is not a valid club invitation.');
        }
        
        // Check if player is already in another club
        if ($player->approvedClubMembership) {
            return back()->with('error', 'You are already a member of another club. You must leave your current club before accepting this invitation.');
        }
        
        // Keep status as 'invited' - player will appear in Invited Players section
        // Status will change to 'approved' only after manager assigns provisional skill level
        // No update needed - status remains 'invited' until provisional skill level is assigned
        
        // Notify club manager
        $club = $clubPlayer->club;
        Notification::create([
            'user_id' => $club->manager_id,
            'type' => 'club_invitation_accepted',
            'title' => 'Club Invitation Accepted',
            'message' => ($player->first_name . ' ' . $player->last_name) . " has accepted your invitation to join {$club->name}. Please assign a provisional skill level.",
            'data' => [
                'club_id' => $club->id,
                'player_id' => $player->id,
            ],
            'action_url' => route('manager.club'),
        ]);
        
        return redirect()->route('clubs.index')
            ->with('success', "You have successfully joined {$clubPlayer->club->name}! The club manager will assign your provisional skill level shortly.");
    }
    
    /**
     * Reject a club invitation.
     */
    public function rejectInvitation(ClubPlayer $clubPlayer): RedirectResponse
    {
        $player = auth()->user();
        
        // Verify this invitation belongs to the current player
        if ($clubPlayer->player_id !== $player->id) {
            abort(403, 'Unauthorized. This invitation does not belong to you.');
        }
        
        // Verify this is an invitation (not a join request)
        if ($clubPlayer->status !== 'invited' || $clubPlayer->request_type !== 'invitation') {
            return back()->with('error', 'This is not a valid club invitation.');
        }
        
        // Update status to rejected
        $clubPlayer->update(['status' => 'rejected']);
        
        // Notify club manager
        Notification::create([
            'user_id' => $clubPlayer->club->manager_id,
            'type' => 'club_invitation_rejected',
            'title' => 'Club Invitation Rejected',
            'message' => ($player->first_name . ' ' . $player->last_name) . " has declined your invitation to join {$clubPlayer->club->name}.",
            'data' => [
                'club_id' => $clubPlayer->club_id,
                'player_id' => $player->id,
            ],
            'action_url' => route('manager.club'),
        ]);
        
        return redirect()->route('clubs.index')
            ->with('success', "You have declined the invitation from {$clubPlayer->club->name}.");
    }
    
    
    /**
     * Create official ELO rating from provisional skill level
     */
    protected function createEloFromProvisional(\App\Models\User $player, int $eloRating): void
    {
        // Determine category based on player gender (default to MS/WS)
        $category = $player->gender === 'Female' ? 'WS' : 'MS';
        
        // Create or update ELO rating
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
        
        // Create ranking history entry
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
