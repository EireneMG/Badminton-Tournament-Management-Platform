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
        
        if (!$tournament->club || $tournament->club->manager_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }
        
        $request->validate([
            'status' => ['required', 'in:pending,eligible,approved,rejected'],
        ]);
        
        $oldStatus = $registration->status;
        $newStatus = $request->status;
        
        if ($newStatus === 'approved' || $newStatus === 'eligible') {
            $eligibilityService = app(\App\Services\EligibilityService::class);
            $eligibility = $eligibilityService->checkEligibility(
                $registration->player,
                $registration->category,
                $registration->partner
            );
            
            if (!$eligibility['eligible']) {
                return back()->with('error', 'Cannot approve registration: ' . implode(' ', $eligibility['reasons']));
            }
        }
        
        $registration->update(['status' => $newStatus]);
        
        $notificationMessages = [
            'eligible' => [
                'title' => 'Eligibility Approved',
                'message' => "You are eligible for {$tournament->name}!",
                'type' => 'registration_eligible',
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
            $notification = Notification::create([
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

            // Send email notification
            app(\App\Services\EmailService::class)->sendNotificationEmail($notification);
        }
        
        return back()->with('success', 'Registration status updated successfully!');
    }


    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'registration_ids' => ['required', 'array'],
            'registration_ids.*' => ['exists:tournament_registrations,id'],
            'status' => ['required', 'in:pending,eligible,approved,rejected'],
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
                    'message' => "You are eligible for {$tournament->name}!",
                    'type' => 'registration_eligible',
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
                $notification = Notification::create([
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

                // Send email notification
                app(\App\Services\EmailService::class)->sendNotificationEmail($notification);
            }
        }
        
        return back()->with('success', 'Registration statuses updated successfully!');
    }
}
