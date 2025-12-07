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

        // Get all tournaments for this club
        $tournamentIds = \App\Models\Tournament::where('club_id', $club->id)
            ->pluck('id');

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
            ->orderBy('created_at', 'desc')
            ->get();

        return view('manager.withdrawals.index', compact('withdrawalRequests', 'club'));
    }

    /**
     * Show withdrawal request details.
     */
    public function show(WithdrawalRequest $withdrawalRequest): View
    {
        // Verify manager owns this tournament
        $registration = $withdrawalRequest->tournamentRegistration;
        $tournament = $registration->tournament;
        $club = \App\Models\Club::where('manager_id', auth()->id())->first();

        if (!$club || $tournament->club_id !== $club->id) {
            abort(403, 'Unauthorized access.');
        }

        return view('manager.withdrawals.show', compact('withdrawalRequest', 'registration', 'tournament'));
    }

    /**
     * Approve a withdrawal request.
     */
    public function approve(WithdrawalRequest $withdrawalRequest, Request $request): RedirectResponse
    {
        $registration = $withdrawalRequest->tournamentRegistration;
        $tournament = $registration->tournament;
        $club = \App\Models\Club::where('manager_id', auth()->id())->first();

        if (!$club || $tournament->club_id !== $club->id) {
            abort(403, 'Unauthorized access.');
        }

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
            // Withdraw entire team
            $this->withdrawTeam($registration, $tournament);
        } else {
            // Withdraw individual player
            $this->withdrawIndividual($registration, $tournament);
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
                    'type' => 'withdrawal_approved',
                    'title' => 'Withdrawal Approved',
                    'message' => "Your withdrawal request for {$tournament->name} has been approved.",
                    'data' => [
                        'tournament_id' => $tournament->id,
                        'withdrawal_request_id' => $withdrawalRequest->id,
                    ],
                    'action_url' => route('withdrawal.status'),
                ]);

                // Send email notification
                app(\App\Services\EmailService::class)->sendNotificationEmail($notification);
            } catch (\Exception $e) {
                \Log::warning("Failed to send withdrawal notification to player {$playerId}: " . $e->getMessage());
            }
        }

        return redirect()->route('manager.tournaments.show', $tournament->id)
            ->with('success', 'Withdrawal request approved successfully. Player/team has been withdrawn from the tournament.');
    }

    /**
     * Reject a withdrawal request.
     */
    public function reject(WithdrawalRequest $withdrawalRequest, Request $request): RedirectResponse
    {
        $registration = $withdrawalRequest->tournamentRegistration;
        $tournament = $registration->tournament;
        $club = \App\Models\Club::where('manager_id', auth()->id())->first();

        if (!$club || $tournament->club_id !== $club->id) {
            abort(403, 'Unauthorized access.');
        }

        if ($withdrawalRequest->status !== 'pending') {
            return back()->with('error', 'This withdrawal request has already been processed.');
        }

        $request->validate([
            'manager_response' => ['nullable', 'string', 'max:1000'],
        ]);

        // Update withdrawal request
        $withdrawalRequest->update([
            'status' => 'rejected',
            'refund_status' => 'none',
            'manager_response' => $request->manager_response,
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        // Restore registration status to previous status (before withdrawal_requested)
        // If it was approved, keep it approved; if it was paid, keep it paid
        $previousStatus = $registration->status === 'withdrawal_requested' ? 'approved' : $registration->status;
        if ($previousStatus === 'withdrawal_requested') {
            // Check if there was a previous status stored, otherwise default to approved
            $previousStatus = 'approved';
        }
        $registration->update(['status' => $previousStatus]);

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
                    'action_url' => route('withdrawal.status'),
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
     */
    protected function withdrawTeam($registration, $tournament): void
    {
        // Get all registrations for this team
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
            $teamReg->update(['status' => 'withdrawn']);
        }

        // Cancel matches and advance opponents
        $this->cancelTeamMatches($tournament, $registration);

        // Notify partner
        if ($registration->partner) {
            Notification::create([
                'user_id' => $registration->partner_id,
                'type' => 'team_withdrawn',
                'title' => 'Team Withdrawn',
                'message' => "Your team has been withdrawn from {$tournament->name}.",
                'data' => [
                    'tournament_id' => $tournament->id,
                ],
                'action_url' => route('player.tournaments.show', $tournament->id),
            ]);
        }
    }

    /**
     * Withdraw individual player (singles)
     */
    protected function withdrawIndividual($registration, $tournament): void
    {
        $registration->update(['status' => 'withdrawn']);
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
     */
    protected function cancelPlayerMatches($tournament, $registration): void
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
            // Determine if opponent should advance (walkover)
            $player1Withdrew = ($match->player1_id === $registration->player_id);
            $player2Withdrew = ($match->player2_id === $registration->player_id);
            
            // If only one player withdrew, advance the opponent
            if (($player1Withdrew && !$player2Withdrew) || (!$player1Withdrew && $player2Withdrew)) {
                $advancingPlayerId = $player1Withdrew ? $match->player2_id : $match->player1_id;
                
                // Mark match as completed via walkover and advance opponent
                $match->update([
                    'status' => 'completed',
                    'winner_id' => $advancingPlayerId,
                ]);
                
                $this->matchGenerationService->advanceWinner($match);
            } else {
                // Both players withdrew or match was already in progress - just cancel
                $match->update(['status' => 'cancelled']);
            }
        }
    }
}

