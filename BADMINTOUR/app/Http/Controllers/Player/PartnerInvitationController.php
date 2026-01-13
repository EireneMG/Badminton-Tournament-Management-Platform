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
use App\Services\PartnerMatchingService;
use App\Services\EloRatingService;

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
        $message = $validated['message'] ?? null;

        // If inviter already has a partner for this category, block further invites
        $inviterHasPartner = TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('category_id', $category->id)
            ->where('player_id', $inviter->id)
            ->whereNotNull('partner_id')
            ->whereIn('status', ['pending', 'eligible', 'approved'])
            ->exists();
        if ($inviterHasPartner) {
            return back()->with('error', 'You already have a partner for this category.');
        }

        // Gender rule enforcement (strict, includes mixed opposite)
        // Use category type attribute which correctly maps MS/WS/MD/WD/XD
        $catType = strtoupper($category->type ?? '');
        $catName = strtolower($category->name ?? '');
        $catMatch = strtolower($category->match_type ?? '');
        // Normalize genders to handle both 'Male'/'Female' and 'male'/'female'
        $inviteeGender = ucfirst(strtolower($invitee->gender ?? ''));
        $inviterGender = ucfirst(strtolower($inviter->gender ?? ''));
        
        // Determine category type - prioritize type attribute, fallback to name
        $isMixed = $catType === 'XD' || str_contains($catName, 'mixed') || str_contains($catMatch, 'mixed');
        $forceMale = $catType === 'MD' || str_contains($catName, "men's") || str_contains($catName, 'mens') || str_contains($catMatch, 'men');
        $forceFemale = $catType === 'WD' || str_contains($catName, "women's") || str_contains($catName, 'womens') || str_contains($catMatch, 'women');

        if ($isMixed) {
            // For mixed doubles: inviter and invitee must be opposite genders
            if ($inviterGender === 'Male' && $inviteeGender !== 'Female') {
                return back()->with('error', 'For mixed doubles, you must invite a female partner.');
            }
            if ($inviterGender === 'Female' && $inviteeGender !== 'Male') {
                return back()->with('error', 'For mixed doubles, you must invite a male partner.');
            }
        } elseif ($forceMale) {
            // For men's doubles: only males allowed
            if ($inviteeGender !== 'Male') {
                return back()->with('error', 'You can only invite male players for this category.');
            }
        } elseif ($forceFemale) {
            // For women's doubles: only females allowed
            if ($inviteeGender !== 'Female') {
                return back()->with('error', 'You can only invite female players for this category.');
            }
        }

        // Skill-level restriction: exact match unless open/all
        $skillReq = strtolower($category->skill_level ?? '');
        if ($skillReq && !in_array($skillReq, ['open','all','any'])) {
            $inviteeSkill = strtolower($invitee->skill_level ?? '');
            if ($inviteeSkill !== $skillReq) {
                return back()->with('error', 'This category requires a partner with skill level: ' . ucfirst($skillReq) . '.');
            }
        }

        // Block if invitee already registered in this tournament (any category)
        $inviteeAnyTournamentRegistration = TournamentRegistration::where('tournament_id', $tournament->id)
            ->where(function($q) use ($invitee) {
                $q->where('player_id', $invitee->id)->orWhere('partner_id', $invitee->id);
            })
            ->whereIn('status', ['pending', 'eligible', 'approved'])
            ->exists();
        if ($inviteeAnyTournamentRegistration) {
            return back()->with('error', 'This player is already registered in this tournament and cannot be invited.');
        }

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
            ->whereIn('status', ['pending', 'eligible', 'approved'])
            ->first();

        if ($existingRegistration) {
            return back()->with('error', 'This player is already registered and linked with a partner in this category.');
        }

        // Block if invitee already has a partner registration
        $inviteeHasPartner = TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('category_id', $category->id)
            ->where(function($q) use ($invitee) {
                $q->where('player_id', $invitee->id)->orWhere('partner_id', $invitee->id);
            })
            ->whereNotNull('partner_id')
            ->whereIn('status', ['pending', 'eligible', 'approved'])
            ->exists();
        if ($inviteeHasPartner) {
            return back()->with('error', 'This player already has a partner for this category.');
        }

        // Block if invitee already registered in this category (even without partner)
        $inviteeCategoryRegistration = TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('category_id', $category->id)
            ->where(function($q) use ($invitee) {
                $q->where('player_id', $invitee->id)->orWhere('partner_id', $invitee->id);
            })
            ->whereIn('status', ['pending', 'eligible', 'approved'])
            ->exists();
        if ($inviteeCategoryRegistration) {
            return back()->with('error', 'The selected partner is already registered in this category.');
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
            'message' => $message,
        ]);

        // Send notification to invitee
        Notification::create([
            'user_id' => $invitee->id,
            'type' => 'partner_invitation',
            'title' => 'Partner Invitation',
            'message' => "{$inviter->first_name} {$inviter->last_name} has invited you to be their partner for {$tournament->name} - {$category->name}." . ($message ? " Message: {$message}" : ''),
            'data' => [
                'tournament_id' => $tournament->id,
                'category_id' => $category->id,
                'invitation_id' => $invitation->id,
            ],
            'action_url' => route('player.tournaments.register.show', ['tournament' => $tournament->id, 'category' => $category->id]),
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

        // Block if invitee already has an accepted invitation with someone ELSE (not the inviter)
        // This prevents accepting multiple invitations before registration
        $inviteeHasOtherAcceptedInvitation = PartnerInvitation::where('tournament_id', $invitation->tournament_id)
            ->where('category_id', $invitation->category_id)
            ->where('status', 'accepted')
            ->where(function($q) use ($invitation) {
                // Invitee has accepted invitation as invitee with a different inviter
                $q->where(function($subQ) use ($invitation) {
                    $subQ->where('invitee_id', $invitation->invitee_id)
                         ->where('inviter_id', '!=', $invitation->inviter_id);
                })
                // OR invitee has accepted invitation as inviter with a different invitee
                ->orWhere(function($subQ) use ($invitation) {
                    $subQ->where('inviter_id', $invitation->invitee_id)
                         ->where('invitee_id', '!=', $invitation->inviter_id);
                });
            })
            ->exists();
        if ($inviteeHasOtherAcceptedInvitation) {
            return back()->with('error', 'You already have an accepted partner invitation for this category.');
        }

        // Block if invitee already has a partner with someone ELSE (not the inviter)
        // Check if invitee is registered with a different partner
        $inviteeHasOtherPartner = TournamentRegistration::where('tournament_id', $invitation->tournament_id)
            ->where('category_id', $invitation->category_id)
            ->where(function($q) use ($invitation) {
                // Invitee is registered as player with a different partner
                $q->where(function($subQ) use ($invitation) {
                    $subQ->where('player_id', $invitation->invitee_id)
                         ->whereNotNull('partner_id')
                         ->where('partner_id', '!=', $invitation->inviter_id);
                })
                // OR invitee is registered as partner with a different player
                ->orWhere(function($subQ) use ($invitation) {
                    $subQ->where('partner_id', $invitation->invitee_id)
                         ->where('player_id', '!=', $invitation->inviter_id);
                });
            })
            ->whereIn('status', ['pending', 'eligible', 'approved'])
            ->exists();
        if ($inviteeHasOtherPartner) {
            return back()->with('error', 'You already have a partner for this category.');
        }

        // Gender rule enforcement on acceptance (safety)
        // Use category type attribute which correctly maps MS/WS/MD/WD/XD
        $catType = strtoupper($invitation->category->type ?? '');
        $catName = strtolower($invitation->category->name ?? '');
        $catMatch = strtolower($invitation->category->match_type ?? '');
        
        // Determine category type - prioritize type attribute, fallback to name
        $isMixed = $catType === 'XD' || str_contains($catName, 'mixed') || str_contains($catMatch, 'mixed');
        $forceMale = $catType === 'MD' || str_contains($catName, "men's") || str_contains($catName, 'mens') || str_contains($catMatch, 'men');
        $forceFemale = $catType === 'WD' || str_contains($catName, "women's") || str_contains($catName, 'womens') || str_contains($catMatch, 'women');
        
        $invitee = $invitation->invitee;
        $inviter = $invitation->inviter;
        
        // Normalize genders to handle both 'Male'/'Female' and 'male'/'female'
        $inviterGender = $inviter ? ucfirst(strtolower($inviter->gender ?? '')) : '';
        $inviteeGender = $invitee ? ucfirst(strtolower($invitee->gender ?? '')) : '';
        
        // Validate gender rules
        if ($isMixed) {
            // For mixed doubles: invitee must be opposite gender of inviter
            if ($inviter && $invitee) {
                if (($inviterGender === 'Male' && $inviteeGender !== 'Female') || 
                    ($inviterGender === 'Female' && $inviteeGender !== 'Male')) {
                    return back()->with('error', 'For mixed doubles, you must be the opposite gender of your partner.');
                }
            }
        } elseif ($forceMale) {
            // For men's doubles: only males can accept
            if ($invitee && $inviteeGender !== 'Male') {
                return back()->with('error', 'Only male players can join this category.');
            }
        } elseif ($forceFemale) {
            // For women's doubles: only females can accept
            if ($invitee && $inviteeGender !== 'Female') {
                return back()->with('error', 'Only female players can join this category.');
            }
        }


        // Check if withdrawal deadline has passed
        $tournament = $invitation->tournament;
        if ($tournament->withdrawal_deadline && now()->isAfter($tournament->withdrawal_deadline)) {
            $invitation->update(['status' => 'expired']);
            return back()->with('error', 'The withdrawal deadline has passed. This invitation has expired.');
        }

        // DO NOT create registration here - only update invitation status
        // Registration will be created when Player A confirms registration

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
            'message' => "{$invitation->invitee->first_name} {$invitation->invitee->last_name} has accepted your partner invitation for {$tournament->name} - {$invitation->category->name}. You can now proceed to confirm your registration.",
            'data' => [
                'tournament_id' => $tournament->id,
                'category_id' => $invitation->category_id,
            ],
            'action_url' => route('player.tournaments.register.show', ['tournament' => $tournament->id, 'category' => $invitation->category_id]),
        ]);

        return redirect()
            ->route('player.tournaments.register.show', ['tournament' => $tournament->id, 'category' => $invitation->category_id])
            ->with('success', 'Partner invitation accepted! You can now proceed to confirm your registration.');
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

        // Attempt auto-partnering for both players if they have open registrations without partners
        $matchService = app(PartnerMatchingService::class);
        $inviterReg = TournamentRegistration::where('tournament_id', $invitation->tournament_id)
            ->where('category_id', $invitation->category_id)
            ->where('player_id', $invitation->inviter_id)
            ->whereNull('partner_id')
            ->first();
        if ($inviterReg) {
            $matchService->attemptAutoPartner($inviterReg);
        }
        $inviteeReg = TournamentRegistration::where('tournament_id', $invitation->tournament_id)
            ->where('category_id', $invitation->category_id)
            ->where('player_id', $invitation->invitee_id)
            ->whereNull('partner_id')
            ->first();
        if ($inviteeReg) {
            $matchService->attemptAutoPartner($inviteeReg);
        }

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
            'action_url' => route('player.tournaments.register.show', ['tournament' => $tournament->id, 'category' => $invitation->category_id]),
        ]);

        return redirect()
            ->route('player.tournaments.register.show', ['tournament' => $tournament->id, 'category' => $invitation->category_id])
            ->with('success', 'Partner invitation rejected.');
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
