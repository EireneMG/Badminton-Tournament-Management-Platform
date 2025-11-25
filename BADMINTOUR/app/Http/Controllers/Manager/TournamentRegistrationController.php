<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\TournamentRegistration;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TournamentRegistrationController extends Controller
{
    public function updateStatus(TournamentRegistration $registration, Request $request): RedirectResponse
    {
        $tournament = $registration->tournament;
        
        if ($tournament->club->manager_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }
        
        $request->validate([
            'status' => ['required', 'in:pending,eligible,awaiting_payment,paid,approved,rejected'],
        ]);
        
        $oldStatus = $registration->status;
        $newStatus = $request->status;
        
        $registration->update(['status' => $newStatus]);
        
        $notificationMessages = [
            'eligible' => [
                'title' => 'Eligibility Approved',
                'message' => "You are eligible for {$tournament->name}! Please complete payment to proceed.",
                'type' => 'registration_eligible',
            ],
            'awaiting_payment' => [
                'title' => 'Payment Required',
                'message' => "Your registration for {$tournament->name} is approved. Please contact the manager to complete payment. Fee: ₱{$tournament->tournament_fee}",
                'type' => 'registration_awaiting_payment',
            ],
            'paid' => [
                'title' => 'Payment Confirmed',
                'message' => "Your payment for {$tournament->name} has been confirmed by the manager.",
                'type' => 'registration_paid',
            ],
            'approved' => [
                'title' => 'Registration Approved',
                'message' => "Your registration for {$tournament->name} has been approved! Good luck!",
                'type' => 'registration_approved',
            ],
            'rejected' => [
                'title' => 'Registration Rejected',
                'message' => "Unfortunately, your registration for {$tournament->name} was not approved.",
                'type' => 'registration_rejected',
            ],
        ];
        
        if (isset($notificationMessages[$newStatus])) {
            $notif = $notificationMessages[$newStatus];
            Notification::create([
                'user_id' => $registration->player_id,
                'type' => $notif['type'],
                'title' => $notif['title'],
                'message' => $notif['message'],
                'data' => [
                    'tournament_id' => $tournament->id,
                    'registration_id' => $registration->id,
                ],
                'action_url' => route('player.tournaments.show', $tournament->id),
            ]);
        }
        
        return back()->with('success', 'Registration status updated successfully!');
    }

    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'registration_ids' => ['required', 'array'],
            'registration_ids.*' => ['exists:tournament_registrations,id'],
            'status' => ['required', 'in:pending,eligible,awaiting_payment,paid,approved,rejected'],
        ]);
        
        $registrations = TournamentRegistration::whereIn('id', $request->registration_ids)->get();
        
        foreach ($registrations as $registration) {
            if ($registration->tournament->club->manager_id !== auth()->id()) {
                continue;
            }
            
            $registration->update(['status' => $request->status]);
            
            $tournament = $registration->tournament;
            $notificationMessages = [
                'eligible' => [
                    'title' => 'Eligibility Approved',
                    'message' => "You are eligible for {$tournament->name}! Please complete payment to proceed.",
                    'type' => 'registration_eligible',
                ],
                'awaiting_payment' => [
                    'title' => 'Payment Required',
                    'message' => "Your registration for {$tournament->name} is approved. Please contact the manager to complete payment. Fee: ₱{$tournament->tournament_fee}",
                    'type' => 'registration_awaiting_payment',
                ],
                'paid' => [
                    'title' => 'Payment Confirmed',
                    'message' => "Your payment for {$tournament->name} has been confirmed.",
                    'type' => 'registration_paid',
                ],
                'approved' => [
                    'title' => 'Registration Approved',
                    'message' => "Your registration for {$tournament->name} has been approved!",
                    'type' => 'registration_approved',
                ],
                'rejected' => [
                    'title' => 'Registration Rejected',
                    'message' => "Your registration for {$tournament->name} was not approved.",
                    'type' => 'registration_rejected',
                ],
            ];
            
            if (isset($notificationMessages[$request->status])) {
                $notif = $notificationMessages[$request->status];
                Notification::create([
                    'user_id' => $registration->player_id,
                    'type' => $notif['type'],
                    'title' => $notif['title'],
                    'message' => $notif['message'],
                    'data' => [
                        'tournament_id' => $tournament->id,
                        'registration_id' => $registration->id,
                    ],
                    'action_url' => route('player.tournaments.show', $tournament->id),
                ]);
            }
        }
        
        return back()->with('success', 'Registration statuses updated successfully!');
    }
}
