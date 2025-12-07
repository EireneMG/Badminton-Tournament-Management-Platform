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

        // Check withdrawal deadline
        if ($tournament->withdrawal_deadline && Carbon::now()->isAfter($tournament->withdrawal_deadline)) {
            return back()->with('error', 'The withdrawal deadline has passed. You cannot submit a withdrawal request.');
        }

        // Check if tournament has started
        if (Carbon::now()->isAfter($tournament->start_date)) {
            return back()->with('error', 'The tournament has started. Withdrawals are no longer allowed.');
        }

        // Create withdrawal request
        $withdrawalRequest = WithdrawalRequest::create([
            'tournament_registration_id' => $registration->id,
            'reason' => $request->reason,
            'status' => 'pending',
            'refund_status' => 'pending',
        ]);

        // Update registration status to "withdrawal_requested"
        $registration->update(['status' => 'withdrawal_requested']);

        // Notify manager
        $manager = $tournament->club->manager;
        Notification::create([
            'user_id' => $manager->id,
            'type' => 'withdrawal_requested',
            'title' => 'New Withdrawal Request',
            'message' => auth()->user()->first_name . ' ' . auth()->user()->last_name . " has submitted a withdrawal request for {$tournament->name}.",
            'data' => [
                'tournament_id' => $tournament->id,
                'withdrawal_request_id' => $withdrawalRequest->id,
            ],
            'action_url' => route('manager.withdrawals.show', $withdrawalRequest->id),
        ]);

        return redirect()->route('withdrawal.status')
            ->with('success', 'Your withdrawal request has been submitted and is pending manager review.');
    }
}
