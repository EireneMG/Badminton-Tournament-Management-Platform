<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\PartnerInvitation;
use App\Models\Tournament;
use App\Models\User;
use App\Models\ClubPlayer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvitationController extends Controller
{
    /**
     * Show invitation management page
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        
        // Get invitations sent by user
        $sentInvitations = PartnerInvitation::where('inviter_id', $user->id)
            ->with(['tournament', 'category', 'invitee'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get invitations received by user
        $receivedInvitations = PartnerInvitation::where('invitee_id', $user->id)
            ->with(['tournament', 'category', 'inviter'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Filter options
        $statusFilter = $request->get('status', 'all');
        $tournamentFilter = $request->get('tournament_id');
        
        if ($statusFilter !== 'all') {
            $sentInvitations = $sentInvitations->where('status', $statusFilter);
            $receivedInvitations = $receivedInvitations->where('status', $statusFilter);
        }
        
        if ($tournamentFilter) {
            $sentInvitations = $sentInvitations->where('tournament_id', $tournamentFilter);
            $receivedInvitations = $receivedInvitations->where('tournament_id', $tournamentFilter);
        }
        
        $tournaments = Tournament::whereHas('invitations', function($query) use ($user) {
            $query->where('inviter_id', $user->id)
                  ->orWhere('invitee_id', $user->id);
        })->get();
        
        return view('player.invitations.index', compact('sentInvitations', 'receivedInvitations', 'tournaments', 'statusFilter', 'tournamentFilter'));
    }
    
    /**
     * Show partner search page
     */
    public function search(Request $request, Tournament $tournament, $categoryId): View
    {
        $user = auth()->user();
        $category = $tournament->categories()->findOrFail($categoryId);
        
        // Get search filters
        $skillLevel = $request->get('skill_level');
        $clubId = $request->get('club_id');
        $gender = $request->get('gender');
        $search = $request->get('search');
        
        // Build query for eligible partners
        $query = User::where('role', 'player')
            ->where('id', '!=', $user->id)
            ->whereHas('clubMemberships', function($q) {
                $q->where('status', 'approved');
            });
        
        // Exclude already registered players
        $registeredPlayerIds = \App\Models\TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('category_id', $categoryId)
            ->whereIn('status', ['pending_payment', 'paid', 'approved'])
            ->pluck('player_id')
            ->merge(
                \App\Models\TournamentRegistration::where('tournament_id', $tournament->id)
                    ->where('category_id', $categoryId)
                    ->whereIn('status', ['pending_payment', 'paid', 'approved'])
                    ->whereNotNull('partner_id')
                    ->pluck('partner_id')
            )
            ->unique();
        
        $query->whereNotIn('id', $registeredPlayerIds);
        
        // Filter by skill level
        if ($skillLevel) {
            $query->whereHas('clubMemberships', function($q) use ($skillLevel) {
                $q->where('status', 'approved')
                  ->where('skill_level', $skillLevel);
            });
        }
        
        // Filter by club
        if ($clubId) {
            if ($tournament->is_dual_meet) {
                // For interclub: exclude players from same club
                $userClub = ClubPlayer::where('player_id', $user->id)
                    ->where('status', 'approved')
                    ->first();
                
                if ($userClub && $clubId == $userClub->club_id) {
                    $query->whereHas('clubMemberships', function($q) use ($clubId) {
                        $q->where('club_id', '!=', $clubId)
                          ->where('status', 'approved');
                    });
                } else {
                    $query->whereHas('clubMemberships', function($q) use ($clubId) {
                        $q->where('club_id', $clubId)
                          ->where('status', 'approved');
                    });
                }
            } else {
                $query->whereHas('clubMemberships', function($q) use ($clubId) {
                    $q->where('club_id', $clubId)
                      ->where('status', 'approved');
                });
            }
        } else if ($tournament->is_dual_meet) {
            // For interclub: exclude players from same club
            $userClub = ClubPlayer::where('player_id', $user->id)
                ->where('status', 'approved')
                ->first();
            
            if ($userClub) {
                $query->whereHas('clubMemberships', function($q) use ($userClub) {
                    $q->where('club_id', '!=', $userClub->club_id)
                      ->where('status', 'approved');
                });
            }
        }
        
        // Filter by gender
        if ($gender) {
            $query->where('gender', $gender);
        }
        
        // Search by name
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }
        
        $players = $query->with(['clubMemberships.club'])->paginate(20);
        
        $clubs = \App\Models\Club::whereHas('clubPlayers', function($q) {
            $q->where('status', 'approved');
        })->get();
        
        return view('player.invitations.search', compact('tournament', 'category', 'players', 'clubs', 'skillLevel', 'clubId', 'gender', 'search'));
    }
}

