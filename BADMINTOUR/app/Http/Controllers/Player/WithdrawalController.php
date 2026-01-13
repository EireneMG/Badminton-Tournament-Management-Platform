<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\TournamentRegistration;
use App\Models\WithdrawalRequest;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WithdrawalController extends Controller
{
    /**
     * Store a withdrawal request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'tournament_registration_id' => ['required', 'exists:tournament_registrations,id'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'reason.required' => 'Please provide a withdrawal reason.',
            'reason.min' => 'The withdrawal reason must be at least 5 characters.',
            'reason.max' => 'The withdrawal reason must not exceed 1000 characters.',
        ]);

        $registration = TournamentRegistration::findOrFail($request->tournament_registration_id);

        // Verify player owns this registration
        if ($registration->player_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        // Check if already withdrawn
        if ($registration->status === 'withdrawn') {
            return back()->with('error', 'You have already withdrawn from this tournament.');
        }

        // Check if withdrawal request already exists
        $existingRequest = WithdrawalRequest::where('tournament_registration_id', $registration->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'You already have a pending withdrawal request for this tournament.');
        }

        $tournament = $registration->tournament;
        
        // Load tournament relationships if needed
        if (!$tournament->relationLoaded('club')) {
            $tournament->load('club.manager');
        }

        // Check withdrawal deadline
        if ($tournament->withdrawal_deadline && Carbon::now()->isAfter($tournament->withdrawal_deadline)) {
            return back()->with('error', 'The withdrawal deadline has passed. You cannot submit a withdrawal request.');
        }

        // Check if tournament has started
        if (Carbon::now()->isAfter($tournament->start_date)) {
            return back()->with('error', 'The tournament has started. Withdrawals are no longer allowed.');
        }

        // Validate and format reason
        $reason = $request->reason;
        if (empty($reason)) {
            return back()->with('error', 'Please provide a withdrawal reason.');
        }
        
        $reason = trim($reason);
        if (strlen($reason) < 5) {
            return back()->with('error', 'The withdrawal reason must be at least 5 characters.');
        }
        
        if (strlen($reason) > 1000) {
            return back()->with('error', 'The withdrawal reason must not exceed 1000 characters.');
        }

        // Create withdrawal request
        $withdrawalRequest = WithdrawalRequest::create([
            'tournament_registration_id' => $registration->id,
            'reason' => $reason,
            'status' => 'pending',
            'refund_status' => 'pending',
        ]);

        // Note: We don't update registration status here because 'withdrawal_requested' is not in the enum
        // The withdrawal request itself tracks the pending status
        // When approved, the status will be changed to 'withdrawn'

        // Notify manager
        $manager = $tournament->club?->manager;
        if (!$manager) {
            \Log::warning("Tournament {$tournament->id} has no club or manager, skipping withdrawal notification");
            return redirect()->route('player.tournaments.show', $tournament->id)
                ->with('error', 'Withdrawal request failed: Tournament manager not found.');
        }
        $notification = Notification::create([
            'user_id' => $manager->id,
            'type' => 'withdrawal_requested',
            'title' => 'New Withdrawal Request',
            'message' => auth()->user()->first_name . ' ' . auth()->user()->last_name . " has submitted a withdrawal request for {$tournament->name}. Reason: " . substr($reason, 0, 100) . (strlen($reason) > 100 ? '...' : ''),
            'data' => [
                'tournament_id' => $tournament->id,
                'withdrawal_request_id' => $withdrawalRequest->id,
                'registration_id' => $registration->id,
            ],
            'action_url' => route('manager.withdrawals.show', $withdrawalRequest->id),
        ]);

        // Send email notification to manager
        try {
            app(\App\Services\EmailService::class)->sendNotificationEmail($notification);
        } catch (\Exception $e) {
            \Log::error('Failed to send withdrawal request email notification', [
                'notification_id' => $notification->id,
                'manager_id' => $manager->id,
                'error' => $e->getMessage()
            ]);
        }

        return redirect()->route('player.tournaments.show', $tournament->id)
            ->with('success', 'Your withdrawal request has been submitted and is pending manager review.');
    }
}
