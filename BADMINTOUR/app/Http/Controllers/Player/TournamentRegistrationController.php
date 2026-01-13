<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterTournamentRequest;
use App\Models\TournamentRegistration;
use App\Models\TournamentCategory;
use App\Models\Tournament;
use App\Models\Notification;
use App\Models\Club;
use App\Models\TournamentMatch;
use App\Models\MatchResult;
use Illuminate\Http\RedirectResponse;
use App\Services\PartnerMatchingService;
use Carbon\Carbon;

class TournamentRegistrationController extends Controller
{
    public function register(RegisterTournamentRequest $request): RedirectResponse
    {
        try {
            $category = TournamentCategory::findOrFail($request->category_id);
            $tournament = $category->tournament;
            $player = auth()->user();
            
            $existingRegistrationsCount = TournamentRegistration::where('category_id', $category->id)
                ->whereIn('status', ['approved'])
                ->count();
            
            if ($existingRegistrationsCount >= $category->slots) {
                return back()->with('error', 'This category is already full.');
            }
            
            // For doubles/mixed categories, enforce partner selection (no solo checkout)
            $name = strtolower($category->name);
            $matchType = strtolower($category->match_type ?? '');
            $type = strtolower($category->type ?? '');
            $isDoublesCategory = str_contains($name, 'doubles') || str_contains($name, 'mixed') ||
                str_contains($matchType, 'double') || str_contains($matchType, 'mixed') ||
                in_array($type, ['md', 'wd', 'xd', 'doubles', 'mixed'], true);

            if ($isDoublesCategory && !$request->partner_id) {
                return back()->with('error', 'You must invite and link a confirmed partner before registering for this doubles/mixed category.');
            }
            
            $registration = TournamentRegistration::create([
                'tournament_id' => $tournament->id,
                'category_id' => $category->id,
                'player_id' => $player->id,
                'partner_id' => $request->partner_id,
                'status' => 'pending', // Start as pending, will be updated by eligibility check
            ]);
            
            // Automatically check eligibility using EligibilityService
            // Note: Eligibility was already checked in RegisterTournamentRequest@authorize()
            // This call updates the registration status based on eligibility
            $eligibilityService = app(\App\Services\EligibilityService::class);
            $isEligible = $eligibilityService->checkAndUpdateEligibility($registration);
            
            // Refresh registration to get updated status
            $registration->refresh();
            
            // No auto-assignment: partner must be confirmed before registration

            if ($isEligible) {
                $notification = Notification::create([
                    'user_id' => $player->id,
                    'type' => 'registration_eligible',
                    'title' => 'Registration Eligible',
                    'message' => "Your registration for {$tournament->name} is eligible. Awaiting manager approval.",
                    'data' => [
                        'tournament_id' => $tournament->id,
                        'registration_id' => $registration->id,
                    ],
                    'action_url' => route('player.tournaments.show', $tournament->id),
                ]);
                app(\App\Services\EmailService::class)->sendNotificationEmail($notification);
            } else {
                Notification::create([
                    'user_id' => $player->id,
                    'type' => 'registration_pending',
                    'title' => 'Registration Pending Review',
                    'message' => "Your registration for {$tournament->name} is pending eligibility verification.",
                    'data' => [
                        'tournament_id' => $tournament->id,
                        'registration_id' => $registration->id,
                    ],
                    'action_url' => route('player.tournaments.show', $tournament->id),
                ]);
            }
            
            $manager = $tournament->club?->manager;
            if (!$manager) {
                \Log::warning("Tournament {$tournament->id} has no club or manager, skipping notification");
                return redirect()->route('player.tournaments.show', $tournament->id)
                    ->with('error', 'Registration failed: Tournament manager not found.');
            }
            Notification::create([
                'user_id' => $manager->id,
                'type' => 'registration_submitted',
                'title' => 'New Tournament Registration',
                'message' => $player->first_name . ' ' . $player->last_name . " has registered for {$tournament->name}. Status: " . ($isEligible ? 'Eligible' : 'Pending Review'),
                'data' => [
                    'tournament_id' => $tournament->id,
                    'registration_id' => $registration->id,
                ],
                'action_url' => route('manager.tournaments.show', $tournament->id),
            ]);
            
            // If this is a dual meet tournament and player belongs to a different club, notify player's club manager
            if ($tournament->is_dual_meet) {
                $playerClubMembership = \App\Models\ClubPlayer::where('player_id', $player->id)
                    ->where('status', 'approved')
                    ->with('club')
                    ->first();
                
                // Only notify if player belongs to a club and it's different from the tournament's hosting club
                if ($playerClubMembership && $playerClubMembership->club_id !== $tournament->club_id) {
                    $playerClubManager = $playerClubMembership->club?->manager;
                    
                    Notification::create([
                        'user_id' => $playerClubManager->id,
                        'type' => 'dual_meet_registration',
                        'title' => 'Player Registered in Dual Meet Tournament',
                        'message' => "One of your players, {$player->first_name} {$player->last_name}, has registered for the dual meet tournament: {$tournament->name} (hosted by " . ($tournament->club?->name ?? 'Unknown Club') . ").",
                        'data' => [
                            'tournament_id' => $tournament->id,
                            'registration_id' => $registration->id,
                            'player_id' => $player->id,
                        ],
                        'action_url' => route('manager.tournaments.show', $tournament->id),
                    ]);
                }
            }
            
            $message = $isEligible
                ? "Registration successful! Your registration is eligible and awaiting manager approval."
                : 'Registration submitted successfully! Pending eligibility review.';

            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to register: ' . $e->getMessage());
        }
    }

    public function cancel(TournamentRegistration $registration): RedirectResponse
    {
        if ($registration->player_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }
        
        if (!in_array($registration->status, ['pending'])) {
            return back()->with('error', 'Cannot cancel registration at this stage.');
        }
        
        $registration->delete();
        
        return back()->with('success', 'Registration cancelled successfully.');
    }

    public function withdraw(TournamentRegistration $registration): RedirectResponse
    {
        if ($registration->player_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }
        
        if (!in_array($registration->status, ['approved', 'pending', 'eligible'])) {
            return back()->with('error', 'You can only withdraw from approved or pending registrations.');
        }
        
        $tournament = $registration->tournament;
        
        // Check withdrawal deadline - BLOCK if passed
        if ($tournament->withdrawal_deadline && Carbon::now()->isAfter($tournament->withdrawal_deadline)) {
            return back()->with('error', 'The withdrawal deadline has passed. You cannot withdraw from this tournament.');
        }
        
        // Check if tournament has started - BLOCK withdrawals after start
        if (Carbon::now()->isAfter($tournament->start_date)) {
            return back()->with('error', 'The tournament has started. Withdrawals are no longer allowed.');
        }
        
        $category = $registration->category;
        $hasPartner = $registration->partner_id !== null;
        $isDoublesCategory = $category && (
            str_contains(strtolower($category->name), 'doubles') || 
            str_contains(strtolower($category->name), 'mixed')
        );
        
        // For doubles/mixed teams: Automatically withdraw BOTH partners
        if ($hasPartner && $isDoublesCategory) {
            return $this->withdrawTeam($registration, $tournament);
        } else {
            // Singles - individual withdrawal
            return $this->withdrawIndividual($registration, $tournament);
        }
    }
    
    /**
     * Withdraw entire team (doubles/mixed)
     * Automatically withdraws BOTH partners when one requests withdrawal
     */
    protected function withdrawTeam(TournamentRegistration $registration, Tournament $tournament): RedirectResponse
    {
        $playerA = $registration->player_id;
        $playerB = $registration->partner_id;
        
        if (!$playerA || !$playerB) {
            return $this->withdrawIndividual($registration, $tournament);
        }
        
        // Find ALL registrations for this team in this category
        $teamRegistrations = \App\Models\TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('category_id', $registration->category_id)
            ->where(function($query) use ($playerA, $playerB) {
                $query->where(function($q) use ($playerA, $playerB) {
                    $q->where('player_id', $playerA)->where('partner_id', $playerB);
                })->orWhere(function($q) use ($playerA, $playerB) {
                    $q->where('player_id', $playerB)->where('partner_id', $playerA);
                });
            })
            ->get();
        
        // Withdraw all found team registrations
        foreach ($teamRegistrations as $teamReg) {
            $teamReg->status = 'withdrawn';
            $teamReg->save();
        }
        
        // If Partner B doesn't have their own registration record, create one with withdrawn status
        // This ensures Partner B shows as withdrawn in the registration list
        $partnerBHasRegistration = $teamRegistrations->contains(function($reg) use ($playerB) {
            return $reg->player_id === $playerB;
        });
        
        if (!$partnerBHasRegistration) {
            // Create a withdrawn registration for Partner B to ensure they show as withdrawn
            \App\Models\TournamentRegistration::updateOrCreate(
                [
                    'tournament_id' => $tournament->id,
                    'category_id' => $registration->category_id,
                    'player_id' => $playerB,
                ],
                [
                    'partner_id' => $playerA,
                    'status' => 'withdrawn',
                ]
            );
        }
        
        // Cancel all matches involving this team and advance opponents
        $this->cancelTeamMatches($tournament, $registration);
        
        // Notify both players
        $withdrawingPlayer = $registration->player;
        $partner = $registration->partner;
        
        // Notify the withdrawing player
        Notification::create([
            'user_id' => $playerA,
            'type' => 'team_withdrawn',
            'title' => 'Team Withdrawn',
            'message' => "You and your partner have been withdrawn from {$tournament->name}.",
            'data' => [
                'tournament_id' => $tournament->id,
                'category_id' => $registration->category_id,
            ],
            'action_url' => route('player.tournaments.show', $tournament->id),
        ]);
        
        // Notify the partner
        if ($partner) {
            Notification::create([
                'user_id' => $registration->partner_id,
                'type' => 'team_withdrawn',
                'title' => 'Team Withdrawn',
                'message' => "You and your partner, {$withdrawingPlayer->first_name} {$withdrawingPlayer->last_name}, have been automatically withdrawn from {$tournament->name} because your partner requested withdrawal.",
                'data' => [
                    'tournament_id' => $tournament->id,
                    'category_id' => $registration->category_id,
                ],
                'action_url' => route('player.tournaments.show', $tournament->id),
            ]);
        }
        
        return back()->with('success', 'You have been withdrawn from the tournament. Your partner has also been automatically withdrawn.');
    }
    
    /**
     * Withdraw individual player (singles)
     */
    protected function withdrawIndividual(TournamentRegistration $registration, Tournament $tournament): RedirectResponse
    {
        $registration->update(['status' => 'withdrawn']);
        
        // Cancel matches involving this player
        $this->cancelPlayerMatches($tournament, $registration);
        
        // Notify manager
        $manager = $tournament->club?->manager;
        if (!$manager) {
            \Log::warning("Tournament {$tournament->id} has no club or manager, skipping withdrawal notification");
            return redirect()->route('player.tournaments.show', $tournament->id)
                ->with('error', 'Withdrawal request failed: Tournament manager not found.');
        }
        Notification::create([
            'user_id' => $manager->id,
            'type' => 'withdrawal_requested',
            'title' => 'Player Withdrawal',
            'message' => auth()->user()->first_name . ' ' . auth()->user()->last_name . " has withdrawn from {$tournament->name}.",
            'data' => [
                'tournament_id' => $tournament->id,
                'registration_id' => $registration->id,
            ],
            'action_url' => route('manager.tournaments.show', $tournament->id),
        ]);
        
        return back()->with('success', 'You have successfully withdrawn from the tournament.');
    }
    
    /**
     * Cancel matches for a withdrawn team
     */
    protected function cancelTeamMatches(Tournament $tournament, TournamentRegistration $registration): void
    {
        $withdrawnPlayerIds = [$registration->player_id];
        if ($registration->partner_id) {
            $withdrawnPlayerIds[] = $registration->partner_id;
        }
        
        $matches = TournamentMatch::where('tournament_id', $tournament->id)
            ->where('tournament_category_id', $registration->category_id)
            ->where(function($query) use ($withdrawnPlayerIds) {
                $query->where(function($q) use ($withdrawnPlayerIds) {
                    $q->whereIn('player1_id', $withdrawnPlayerIds)
                      ->orWhereIn('player1_partner_id', $withdrawnPlayerIds);
                })
                ->orWhere(function($q) use ($withdrawnPlayerIds) {
                    $q->whereIn('player2_id', $withdrawnPlayerIds)
                      ->orWhereIn('player2_partner_id', $withdrawnPlayerIds);
                });
            })
            ->where('status', '!=', 'completed')
            ->get();
        
        foreach ($matches as $match) {
            // Determine if opponent should advance (walkover)
            $player1Withdrew = in_array($match->player1_id, $withdrawnPlayerIds) || 
                              ($match->player1_partner_id && in_array($match->player1_partner_id, $withdrawnPlayerIds));
            $player2Withdrew = in_array($match->player2_id, $withdrawnPlayerIds) || 
                              ($match->player2_partner_id && in_array($match->player2_partner_id, $withdrawnPlayerIds));
            
            // If only one team withdrew, advance the opponent
            if (($player1Withdrew && !$player2Withdrew) || (!$player1Withdrew && $player2Withdrew)) {
                $this->advanceOpponent($match, $registration);
            } else {
                // Both teams withdrew or match was already in progress - just cancel
                // Delete any recorded scores
                if ($match->result) {
                    $match->result->delete();
                }
                
                // Mark match as cancelled
                $match->update(['status' => 'cancelled']);
            }
        }
    }
    
    /**
     * Cancel matches for a withdrawn player
     */
    protected function cancelPlayerMatches(Tournament $tournament, TournamentRegistration $registration): void
    {
        $matches = TournamentMatch::where('tournament_id', $tournament->id)
            ->where('tournament_category_id', $registration->category_id)
            ->where(function($query) use ($registration) {
                $query->where('player1_id', $registration->player_id)
                      ->orWhere('player2_id', $registration->player_id);
            })
            ->where('status', '!=', 'completed')
            ->get();
        
        foreach ($matches as $match) {
            if (in_array($match->status, ['scheduled', 'ongoing', null])) {
                $this->advanceOpponent($match, $registration);
            }
            
            if ($match->result) {
                $match->result->delete();
            }
            
            $match->update([
                'status' => 'cancelled',
                'player1_id' => null,
                'player2_id' => null,
                'player1_partner_id' => null,
                'player2_partner_id' => null,
                'winner_id' => null,
                'winner_partner_id' => null,
            ]);
        }
    }
    
    /**
     * Advance opponent when a player/team withdraws (bye logic)
     */
    protected function advanceOpponent(\App\Models\TournamentMatch $match, TournamentRegistration $withdrawnRegistration): void
    {
        // Determine which player/team should advance BEFORE updating match status
        $advancingPlayerId = null;
        $advancingPartnerId = null;
        
        $withdrawnPlayerIds = [$withdrawnRegistration->player_id];
        if ($withdrawnRegistration->partner_id) {
            $withdrawnPlayerIds[] = $withdrawnRegistration->partner_id;
        }
        
        $player1Withdrew = in_array($match->player1_id, $withdrawnPlayerIds) || 
                          ($match->player1_partner_id && in_array($match->player1_partner_id, $withdrawnPlayerIds));
        
        if ($player1Withdrew) {
            // Player 1/Team 1 withdrew, advance Player 2/Team 2
            $advancingPlayerId = $match->player2_id;
            $advancingPartnerId = $match->player2_partner_id;
        } else {
            // Player 2/Team 2 withdrew, advance Player 1/Team 1
            $advancingPlayerId = $match->player1_id;
            $advancingPartnerId = $match->player1_partner_id;
        }
        
        if ($advancingPlayerId) {
            $scheduleService = app(\App\Services\CategoryScheduleService::class);
            $matchService = app(\App\Services\MatchGenerationService::class);
            
            // Mark match as completed via walkover and advance opponent
            $match->update([
                'status' => 'completed',
                'winner_id' => $advancingPlayerId,
                'winner_partner_id' => $advancingPartnerId,
            ]);
            
            $matchService->advanceWinner($match);
        }
    }
    
    /**
     * Get all registrations that belong to the current team (player + partner).
     */
    protected function getTeamRegistrations(TournamentRegistration $registration)
    {
        $playerIds = array_filter([
            $registration->player_id,
            $registration->partner_id,
        ]);
        
        return TournamentRegistration::where('tournament_id', $registration->tournament_id)
            ->where('category_id', $registration->category_id)
            ->where(function($query) use ($playerIds) {
                $query->whereIn('player_id', $playerIds)
                      ->orWhereIn('partner_id', $playerIds);
            })
            ->get();
    }
}
