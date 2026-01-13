<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Models\TournamentMatch;
use App\Models\Notification;
use App\Services\MatchGenerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class WithdrawalController extends Controller
{
    protected $matchGenerationService;

    public function __construct(MatchGenerationService $matchGenerationService)
    {
        $this->matchGenerationService = $matchGenerationService;
    }

    /**
     * Display all pending withdrawal requests for manager's tournaments.
     * Includes: tournaments owned by manager's club + dual meet tournaments where manager's club participates
     */
    public function index(): View
    {
        $club = \App\Models\Club::where('manager_id', auth()->id())->first();
        
        if (!$club) {
            return view('manager.withdrawals.index', [
                'withdrawalRequests' => collect([]),
                'club' => null
            ]);
        }

        // Get tournaments owned by this club
        $ownedTournamentIds = \App\Models\Tournament::where('club_id', $club->id)
            ->pluck('id')
            ->toArray();

        // Get dual meet tournaments where this club's players are registered
        $dualMeetTournamentIds = \App\Models\Tournament::where('is_dual_meet', true)
            ->whereHas('registrations', function($query) use ($club) {
                $query->where('status', 'approved')
                    ->whereHas('player', function($q) use ($club) {
                        $q->whereHas('clubMemberships', function($clubQuery) use ($club) {
                            $clubQuery->where('club_id', $club->id)->where('status', 'approved');
                        });
                    });
            })
            ->pluck('id')
            ->toArray();

        // Merge tournament IDs
        $tournamentIds = array_unique(array_merge($ownedTournamentIds, $dualMeetTournamentIds));

        if (empty($tournamentIds)) {
            return view('manager.withdrawals.index', [
                'withdrawalRequests' => collect([]),
                'club' => $club
            ]);
        }

        // Get all pending withdrawal requests for these tournaments
        $withdrawalRequests = WithdrawalRequest::whereIn('tournament_registration_id', function($query) use ($tournamentIds) {
                $query->select('id')
                    ->from('tournament_registrations')
                    ->whereIn('tournament_id', $tournamentIds);
            })
            ->where('status', 'pending')
            ->with([
                'tournamentRegistration.player',
                'tournamentRegistration.partner',
                'tournamentRegistration.tournament',
                'tournamentRegistration.category'
            ])
            ->get()
            ->filter(function($withdrawal) use ($club) {
                // For dual meet tournaments, only show if player belongs to manager's club
                $tournament = $withdrawal->tournamentRegistration->tournament;
                if ($tournament->is_dual_meet && $tournament->club_id !== $club->id) {
                    $player = $withdrawal->tournamentRegistration->player;
                    return $player->clubMemberships()
                        ->where('status', 'approved')
                        ->where('club_id', $club->id)
                        ->exists();
                }
                return true;
            })
            ->sortByDesc('created_at')
            ->values();

        return view('manager.withdrawals.index', compact('withdrawalRequests', 'club'));
    }

    /**
     * Show withdrawal request details.
     */
    public function show(WithdrawalRequest $withdrawalRequest): View
    {
        if (!$this->canViewWithdrawal($withdrawalRequest)) {
            abort(403, 'Unauthorized access. Only tournament owner or participating club managers can view withdrawals.');
        }
        
        $registration = $withdrawalRequest->tournamentRegistration;
        $tournament = $registration->tournament;
        
        // Check if current manager is the host (can take action) or just viewing
        $isHost = $this->isHostManager($withdrawalRequest);

        return view('manager.withdrawals.show', compact('withdrawalRequest', 'registration', 'tournament', 'isHost'));
    }

    /**
     * Check if manager is the tournament HOST (strict enforcement)
     * ONLY the tournament host can approve/reject withdrawals
     * Invited club managers in dual meet tournaments can only VIEW withdrawals
     */
    protected function isHostManager(WithdrawalRequest $withdrawalRequest): bool
    {
        $tournament = $withdrawalRequest->tournamentRegistration->tournament;
        return $tournament->club && $tournament->club->manager_id === auth()->id();
    }
    
    /**
     * Check if manager can VIEW withdrawals for this tournament
     * Tournament owner OR participating club manager (for viewing only in dual meet)
     */
    protected function canViewWithdrawal(WithdrawalRequest $withdrawalRequest): bool
    {
        $tournament = $withdrawalRequest->tournamentRegistration->tournament;
        $managerId = auth()->id();
        
        // Tournament owner can always view
        if ($tournament->club && $tournament->club->manager_id === $managerId) {
            return true;
        }
        
        // For dual meet tournaments, check if manager's club owns the player (view only)
        if ($tournament->is_dual_meet) {
            $managerClub = \App\Models\Club::where('manager_id', $managerId)->first();
            if (!$managerClub) {
                return false;
            }
            
            // Check if the player belongs to manager's club
            $player = $withdrawalRequest->tournamentRegistration->player;
            return $player->clubMemberships()
                ->where('status', 'approved')
                ->where('club_id', $managerClub->id)
                ->exists();
        }
        
        return false;
    }

    /**
     * Approve a withdrawal request.
     * STRICT: Only the tournament HOST can approve withdrawals
     */
    public function approve(WithdrawalRequest $withdrawalRequest, Request $request): RedirectResponse
    {
        if (!$this->isHostManager($withdrawalRequest)) {
            abort(403, 'Unauthorized: Only the tournament host manager can approve withdrawals. This tournament is managed by the host club.');
        }
        
        $registration = $withdrawalRequest->tournamentRegistration;
        $tournament = $registration->tournament;

        if ($withdrawalRequest->status !== 'pending') {
            return back()->with('error', 'This withdrawal request has already been processed.');
        }

        $request->validate([
            'manager_response' => ['nullable', 'string', 'max:1000'],
        ]);

        // Update withdrawal request
        $withdrawalRequest->update([
            'status' => 'approved',
            'refund_status' => 'pending', // Refunds handled outside system
            'manager_response' => $request->manager_response,
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        // Withdraw the registration
        $category = $registration->category;
        $hasPartner = $registration->partner_id !== null;
        $isDoublesCategory = $category && (
            str_contains(strtolower($category->name), 'doubles') || 
            str_contains(strtolower($category->name), 'mixed')
        );

        if ($hasPartner && $isDoublesCategory) {
            // For doubles/mixed: Automatically withdraw BOTH partners
            $this->withdrawTeam($registration, $tournament);
        } else {
            // For singles: Withdraw individual player
            $this->withdrawIndividual($registration, $tournament);
        }

        // Notify both players (if doubles) or just the player (if singles)
        $playersToNotify = [$registration->player_id];
        if ($hasPartner && $isDoublesCategory && $registration->partner_id) {
            $playersToNotify[] = $registration->partner_id;
        }
        
        // Notify both players with appropriate messages
        $withdrawingPlayer = $registration->player;
        $partner = $registration->partner;
        
        // Notify the withdrawing player
        try {
            $notification = Notification::create([
                'user_id' => $registration->player_id,
                'type' => 'withdrawal_approved',
                'title' => $hasPartner && $isDoublesCategory ? 'Team Withdrawal Approved' : 'Withdrawal Approved',
                'message' => $hasPartner && $isDoublesCategory
                    ? "Your withdrawal request for {$tournament->name} has been approved. You and your partner have been withdrawn from the tournament."
                    : "Your withdrawal request for {$tournament->name} has been approved. You have been withdrawn from the tournament.",
                'data' => [
                    'tournament_id' => $tournament->id,
                    'withdrawal_request_id' => $withdrawalRequest->id,
                ],
                'action_url' => route('player.tournaments.show', $tournament->id),
            ]);
            app(\App\Services\EmailService::class)->sendNotificationEmail($notification);
        } catch (\Exception $e) {
            \Log::warning("Failed to send withdrawal notification to player {$registration->player_id}: " . $e->getMessage());
        }
        
        // Notify the partner (if doubles/mixed)
        if ($hasPartner && $isDoublesCategory && $partner) {
            try {
                $partnerNotification = Notification::create([
                    'user_id' => $registration->partner_id,
                    'type' => 'team_withdrawn',
                    'title' => 'Team Withdrawn',
                    'message' => "You have been automatically withdrawn from {$tournament->name} because your partner, {$withdrawingPlayer->first_name} {$withdrawingPlayer->last_name}, requested withdrawal and it was approved by the tournament manager.",
                    'data' => [
                        'tournament_id' => $tournament->id,
                        'category_id' => $category->id,
                    ],
                    'action_url' => route('player.tournaments.show', $tournament->id),
                ]);
                app(\App\Services\EmailService::class)->sendNotificationEmail($partnerNotification);
            } catch (\Exception $e) {
                \Log::warning("Failed to send partner withdrawal notification to player {$registration->partner_id}: " . $e->getMessage());
            }
        }

        $successMessage = $hasPartner && $isDoublesCategory
            ? 'Withdrawal request approved. Both players have been automatically withdrawn from the tournament.'
            : 'Withdrawal request approved successfully. Player has been withdrawn from the tournament.';

        return redirect()->route('manager.tournaments.show', $tournament->id)
            ->with('success', $successMessage);
    }

    /**
     * Reject a withdrawal request.
     * STRICT: Only the tournament HOST can reject withdrawals
     */
    public function reject(WithdrawalRequest $withdrawalRequest, Request $request): RedirectResponse
    {
        if (!$this->isHostManager($withdrawalRequest)) {
            abort(403, 'Unauthorized: Only the tournament host manager can reject withdrawals. This tournament is managed by the host club.');
        }
        
        $registration = $withdrawalRequest->tournamentRegistration;
        $tournament = $registration->tournament;

        if ($withdrawalRequest->status !== 'pending') {
            return back()->with('error', 'This withdrawal request has already been processed.');
        }

        $request->validate([
            'manager_response' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        // Update withdrawal request
        $withdrawalRequest->update([
            'status' => 'rejected',
            'refund_status' => 'none',
            'manager_response' => $request->manager_response,
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        // When withdrawal is rejected, ensure player(s) remain in the tournament
        // For doubles/mixed teams, we need to handle both registration records
        $category = $registration->category;
        $hasPartner = $registration->partner_id !== null;
        $isDoublesCategory = $category && (
            str_contains(strtolower($category->name), 'doubles') || 
            str_contains(strtolower($category->name), 'mixed')
        );

        if ($hasPartner && $isDoublesCategory) {
            // For doubles/mixed, ensure all team registrations are set to 'approved'
            $teamRegistrations = \App\Models\TournamentRegistration::where('tournament_id', $tournament->id)
                ->where('category_id', $registration->category_id)
                ->where(function($query) use ($registration) {
                    $query->where(function($q) use ($registration) {
                        $q->where('player_id', $registration->player_id)
                          ->where('partner_id', $registration->partner_id);
                    })->orWhere(function($q) use ($registration) {
                        $q->where('player_id', $registration->partner_id)
                          ->where('partner_id', $registration->player_id);
                    });
                })
                ->get();

            foreach ($teamRegistrations as $teamReg) {
                $teamReg->status = 'approved';
                $teamReg->save();
                $teamReg->refresh();
            }
        } else {
            // For singles, just update this registration
            $registration->status = 'approved';
            $registration->save();
            $registration->refresh();
        }

        // Notify player and partner (if doubles)
        $playersToNotify = [$registration->player_id];
        if ($registration->partner_id) {
            $playersToNotify[] = $registration->partner_id;
        }
        
        foreach ($playersToNotify as $playerId) {
            try {
                $notification = Notification::create([
                    'user_id' => $playerId,
                    'type' => 'withdrawal_rejected',
                    'title' => 'Withdrawal Rejected',
                    'message' => "Your withdrawal request for {$tournament->name} has been rejected. " . ($request->manager_response ? "Reason: {$request->manager_response}" : ''),
                    'data' => [
                        'tournament_id' => $tournament->id,
                        'withdrawal_request_id' => $withdrawalRequest->id,
                    ],
                    'action_url' => route('player.tournaments.show', $tournament->id),
                ]);

                // Send email notification
                app(\App\Services\EmailService::class)->sendNotificationEmail($notification);
            } catch (\Exception $e) {
                \Log::warning("Failed to send withdrawal notification to player {$playerId}: " . $e->getMessage());
            }
        }

        return redirect()->route('manager.tournaments.show', $tournament->id)
            ->with('success', 'Withdrawal request rejected.');
    }

    /**
     * Withdraw entire team (doubles/mixed)
     * Automatically withdraws BOTH partners when one requests withdrawal
     */
    protected function withdrawTeam($registration, $tournament): void
    {
        $playerA = $registration->player_id;
        $playerB = $registration->partner_id;
        
        if (!$playerA || !$playerB) {
            // If no partner, treat as individual withdrawal
            $this->withdrawIndividual($registration, $tournament);
            return;
        }
        
        // Find ALL registrations for this team in this category
        // There may be one registration (A as player, B as partner) or two (A+B and B+A)
        // We need to find and withdraw ALL of them
        $teamRegistrations = \App\Models\TournamentRegistration::where('tournament_id', $tournament->id)
            ->where('category_id', $registration->category_id)
            ->where(function($query) use ($playerA, $playerB) {
                // Find registrations where:
                // 1. (player_id = A AND partner_id = B) OR
                // 2. (player_id = B AND partner_id = A)
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
        
        // Cancel matches and advance opponents
        $this->cancelTeamMatches($tournament, $registration);
    }

    /**
     * Withdraw individual player (singles)
     */
    protected function withdrawIndividual($registration, $tournament): void
    {
        // Update registration status to withdrawn
        $registration->status = 'withdrawn';
        $registration->save();
        
        // Refresh the registration to ensure the update is persisted
        $registration->refresh();
        
        $this->cancelPlayerMatches($tournament, $registration);
    }

    /**
     * Cancel matches for a withdrawn team
     */
    protected function cancelTeamMatches($tournament, $registration): void
    {
        $withdrawnPlayerIds = [$registration->player_id];
        if ($registration->partner_id) {
            $withdrawnPlayerIds[] = $registration->partner_id;
        }

        $matches = TournamentMatch::where('tournament_id', $tournament->id)
            ->where('tournament_category_id', $registration->category_id)
            ->where(function($query) use ($withdrawnPlayerIds) {
                $query->whereIn('player1_id', $withdrawnPlayerIds)
                      ->orWhereIn('player2_id', $withdrawnPlayerIds)
                      ->orWhereIn('player1_partner_id', $withdrawnPlayerIds)
                      ->orWhereIn('player2_partner_id', $withdrawnPlayerIds);
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
                $advancingPlayerId = $player1Withdrew ? $match->player2_id : $match->player1_id;
                $advancingPartnerId = $player1Withdrew ? $match->player2_partner_id : $match->player1_partner_id;
                
                // Mark match as completed via walkover and advance opponent
                $match->update([
                    'status' => 'completed',
                    'winner_id' => $advancingPlayerId,
                    'winner_partner_id' => $advancingPartnerId,
                ]);
                
                $this->matchGenerationService->advanceWinner($match);
            } else {
                // Both teams withdrew or match was already in progress - just cancel
                $match->update(['status' => 'cancelled']);
            }
        }
    }

    /**
     * Cancel matches for a withdrawn player
     * Handles both singles and doubles (checks player_id and partner_id)
     */
    protected function cancelPlayerMatches($tournament, $registration): void
    {
        $withdrawnPlayerId = $registration->player_id;
        
        // Find all matches where this player is involved (as player1, player2, or partner)
        $matches = TournamentMatch::where('tournament_id', $tournament->id)
            ->where('tournament_category_id', $registration->category_id)
            ->where(function($query) use ($withdrawnPlayerId) {
                $query->where('player1_id', $withdrawnPlayerId)
                      ->orWhere('player2_id', $withdrawnPlayerId)
                      ->orWhere('player1_partner_id', $withdrawnPlayerId)
                      ->orWhere('player2_partner_id', $withdrawnPlayerId);
            })
            ->where('status', '!=', 'completed')
            ->get();

        foreach ($matches as $match) {
            // Determine if opponent should advance (walkover)
            // Check if withdrawn player is in team 1 or team 2
            $player1TeamWithdrew = ($match->player1_id === $withdrawnPlayerId) || 
                                   ($match->player1_partner_id === $withdrawnPlayerId);
            $player2TeamWithdrew = ($match->player2_id === $withdrawnPlayerId) || 
                                   ($match->player2_partner_id === $withdrawnPlayerId);
            
            // If only one team withdrew, advance the opponent
            if (($player1TeamWithdrew && !$player2TeamWithdrew) || (!$player1TeamWithdrew && $player2TeamWithdrew)) {
                $advancingPlayerId = $player1TeamWithdrew ? $match->player2_id : $match->player1_id;
                $advancingPartnerId = $player1TeamWithdrew ? $match->player2_partner_id : $match->player1_partner_id;
                
                // Mark match as completed via walkover and advance opponent
                $match->update([
                    'status' => 'completed',
                    'winner_id' => $advancingPlayerId,
                    'winner_partner_id' => $advancingPartnerId,
                ]);
                
                $this->matchGenerationService->advanceWinner($match);
            } else {
                // Both players withdrew or match was already in progress - just cancel
                $match->update(['status' => 'cancelled']);
            }
        }
    }
}

