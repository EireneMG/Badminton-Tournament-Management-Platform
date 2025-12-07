<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\PartnerInvitation;
use App\Models\Tournament;
use App\Models\TournamentCategory;
use App\Models\TournamentRegistration;
use App\Models\Notification;
use App\Models\ClubPlayer;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class PartnerInvitationController extends Controller
{
    /**
     * Send a partner invitation
     */
    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tournament_id' => ['required', 'exists:tournaments,id'],
            'category_id' => ['required', 'exists:tournament_categories,id'],
            'invitee_id' => ['required', 'exists:users,id'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $tournament = Tournament::findOrFail($validated['tournament_id']);
        $category = TournamentCategory::findOrFail($validated['category_id']);
        $inviter = auth()->user();
        $invitee = \App\Models\User::findOrFail($validated['invitee_id']);

        // Check if withdrawal deadline has passed
        if ($tournament->withdrawal_deadline && now()->isAfter($tournament->withdrawal_deadline)) {
            return back()->with('error', 'The withdrawal deadline has passed. You can no longer invite partners.');
        }

        // Validate inviter eligibility
        $inviterClubMembership = ClubPlayer::where('player_id', $inviter->id)
            ->where('status', 'approved')
            ->first();

        if (!$inviterClubMembership) {
            return back()->with('error', 'You must be a member of a club to invite partners.');
        }

        // Validate invitee eligibility
        $inviteeClubMembership = ClubPlayer::where('player_id', $invitee->id)
            ->where('status', 'approved')
            ->first();

        if (!$inviteeClubMembership) {
            return back()->with('error', 'The invited player must be a member of a club.');
        }
        
        // Validate interclub rules if applicable
        if ($tournament->is_dual_meet) {
            // For interclub: partners must be from different clubs
            if ($inviterClubMembership->club_id === $inviteeClubMembership->club_id) {
                return back()->with('error', 'In interclub tournaments, partners must be from different clubs.');
            }
        }

        // Check if invitee is already registered in this category
        $existingRegistration = TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('category_id', $category->id)
            ->where(function($query) use ($invitee) {
                $query->where('player_id', $invitee->id)
                      ->orWhere('partner_id', $invitee->id);
            })
            ->whereIn('status', ['pending_payment', 'paid', 'approved'])
            ->first();

        if ($existingRegistration) {
            return back()->with('error', 'This player is already registered or has a pending registration in this category.');
        }

        // Check if there's already a pending invitation
        $existingInvitation = PartnerInvitation::where('tournament_id', $tournament->id)
            ->where('category_id', $category->id)
            ->where('inviter_id', $inviter->id)
            ->where('invitee_id', $invitee->id)
            ->where('status', 'pending')
            ->first();

        if ($existingInvitation) {
            return back()->with('error', 'You have already sent an invitation to this player.');
        }

        // Check if invitee has already sent an invitation to inviter
        $reverseInvitation = PartnerInvitation::where('tournament_id', $tournament->id)
            ->where('category_id', $category->id)
            ->where('inviter_id', $invitee->id)
            ->where('invitee_id', $inviter->id)
            ->where('status', 'pending')
            ->first();

        if ($reverseInvitation) {
            return back()->with('error', 'This player has already sent you an invitation. Please accept or reject it first.');
        }

        // Create invitation
        $invitation = PartnerInvitation::create([
            'tournament_id' => $tournament->id,
            'category_id' => $category->id,
            'inviter_id' => $inviter->id,
            'invitee_id' => $invitee->id,
            'status' => 'pending',
            'message' => $validated['message'] ?? null,
        ]);

        // Send notification to invitee
        Notification::create([
            'user_id' => $invitee->id,
            'type' => 'partner_invitation',
            'title' => 'Partner Invitation',
            'message' => "{$inviter->first_name} {$inviter->last_name} has invited you to be their partner for {$tournament->name} - {$category->name}." . ($validated['message'] ? " Message: {$validated['message']}" : ''),
            'data' => [
                'tournament_id' => $tournament->id,
                'category_id' => $category->id,
                'invitation_id' => $invitation->id,
            ],
            'action_url' => route('player.invitations.show', $invitation->id),
        ]);

        return back()->with('success', "Invitation sent to {$invitee->name}!");
    }

    /**
     * Accept a partner invitation
     */
    public function accept(PartnerInvitation $invitation): RedirectResponse
    {
        if ($invitation->invitee_id !== auth()->id()) {
            abort(403, 'Unauthorized. You can only accept invitations sent to you.');
        }

        if ($invitation->status !== 'pending') {
            return back()->with('error', 'This invitation is no longer valid.');
        }

        // Check if withdrawal deadline has passed
        $tournament = $invitation->tournament;
        if ($tournament->withdrawal_deadline && now()->isAfter($tournament->withdrawal_deadline)) {
            $invitation->update(['status' => 'expired']);
            return back()->with('error', 'The withdrawal deadline has passed. This invitation has expired.');
        }

        // Check if inviter still has a valid registration or needs to create one
        $inviterRegistration = TournamentRegistration::where('tournament_id', $invitation->tournament_id)
            ->where('category_id', $invitation->category_id)
            ->where('player_id', $invitation->inviter_id)
            ->first();

        if ($inviterRegistration && $inviterRegistration->status === 'withdrawn') {
            // Inviter withdrew, create new registration with partner
            $inviterRegistration->update([
                'partner_id' => $invitation->invitee_id,
                'status' => 'pending_payment',
            ]);
        } elseif (!$inviterRegistration) {
            // Create new registration for inviter with partner
            TournamentRegistration::create([
                'tournament_id' => $invitation->tournament_id,
                'category_id' => $invitation->category_id,
                'player_id' => $invitation->inviter_id,
                'partner_id' => $invitation->invitee_id,
                'status' => 'pending_payment',
            ]);
        } else {
            // Update existing registration with partner
            $inviterRegistration->update([
                'partner_id' => $invitation->invitee_id,
            ]);
        }

        // Update invitation status
        $invitation->update([
            'status' => 'accepted',
            'responded_at' => now(),
        ]);

        // Cancel any other pending invitations from inviter for this category
        PartnerInvitation::where('tournament_id', $invitation->tournament_id)
            ->where('category_id', $invitation->category_id)
            ->where('inviter_id', $invitation->inviter_id)
            ->where('id', '!=', $invitation->id)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        // Cancel any other pending invitations from invitee for this category
        PartnerInvitation::where('tournament_id', $invitation->tournament_id)
            ->where('category_id', $invitation->category_id)
            ->where('invitee_id', $invitation->invitee_id)
            ->where('id', '!=', $invitation->id)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        // Notify inviter
        Notification::create([
            'user_id' => $invitation->inviter_id,
            'type' => 'partner_invitation_accepted',
            'title' => 'Partner Invitation Accepted',
            'message' => "{$invitation->invitee->first_name} {$invitation->invitee->last_name} has accepted your partner invitation for {$tournament->name} - {$invitation->category->name}.",
            'data' => [
                'tournament_id' => $tournament->id,
                'category_id' => $invitation->category_id,
                'registration_id' => $inviterRegistration->id ?? null,
            ],
            'action_url' => route('player.tournaments.show', $tournament->id),
        ]);

        return back()->with('success', 'Partner invitation accepted! Your registration has been updated.');
    }

    /**
     * Reject a partner invitation
     */
    public function reject(PartnerInvitation $invitation): RedirectResponse
    {
        if ($invitation->invitee_id !== auth()->id()) {
            abort(403, 'Unauthorized. You can only reject invitations sent to you.');
        }

        if ($invitation->status !== 'pending') {
            return back()->with('error', 'This invitation is no longer valid.');
        }

        $invitation->update([
            'status' => 'rejected',
            'responded_at' => now(),
        ]);

        // Notify inviter
        $tournament = $invitation->tournament;
        Notification::create([
            'user_id' => $invitation->inviter_id,
            'type' => 'partner_invitation_rejected',
            'title' => 'Partner Invitation Rejected',
            'message' => "{$invitation->invitee->first_name} {$invitation->invitee->last_name} has rejected your partner invitation for {$tournament->name} - {$invitation->category->name}.",
            'data' => [
                'tournament_id' => $tournament->id,
                'category_id' => $invitation->category_id,
            ],
            'action_url' => route('player.tournaments.show', $tournament->id),
        ]);

        return back()->with('success', 'Partner invitation rejected.');
    }

    /**
     * Cancel a sent invitation
     */
    public function cancel(PartnerInvitation $invitation): RedirectResponse
    {
        if ($invitation->inviter_id !== auth()->id()) {
            abort(403, 'Unauthorized. You can only cancel invitations you sent.');
        }

        if ($invitation->status !== 'pending') {
            return back()->with('error', 'This invitation can no longer be cancelled.');
        }

        $invitation->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', 'Invitation cancelled.');
    }

    /**
     * Show invitation details
     */
    public function show(PartnerInvitation $invitation)
    {
        if ($invitation->invitee_id !== auth()->id() && $invitation->inviter_id !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }

        return view('player.invitations.show', compact('invitation'));
    }
}
