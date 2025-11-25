<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterTournamentRequest;
use App\Models\TournamentRegistration;
use App\Models\TournamentCategory;
use App\Models\Tournament;
use App\Models\Notification;
use App\Models\Club;
use Illuminate\Http\RedirectResponse;
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
            
            $registration = TournamentRegistration::create([
                'tournament_id' => $tournament->id,
                'category_id' => $category->id,
                'player_id' => $player->id,
                'partner_id' => $request->partner_id,
                'status' => 'pending',
            ]);
            
            $clubMembership = $player->approvedClubMembership;
            $isEligible = $clubMembership && $clubMembership->status === 'approved';
            
            if ($isEligible) {
                $registration->update(['status' => 'eligible']);
                $registration->update(['status' => 'awaiting_payment']);
                
                Notification::create([
                    'user_id' => $player->id,
                    'type' => 'registration_awaiting_payment',
                    'title' => 'Registration Confirmed - Payment Required',
                    'message' => "Your registration for {$tournament->name} is approved. Please contact the manager to complete payment. Fee: ₱{$tournament->tournament_fee}",
                    'data' => [
                        'tournament_id' => $tournament->id,
                        'registration_id' => $registration->id,
                    ],
                    'action_url' => route('player.tournaments.show', $tournament->id),
                ]);
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
            
            $manager = $tournament->club->manager;
            Notification::create([
                'user_id' => $manager->id,
                'type' => 'registration_submitted',
                'title' => 'New Tournament Registration',
                'message' => $player->first_name . ' ' . $player->last_name . " has registered for {$tournament->name}. Status: " . ($isEligible ? 'Awaiting Payment' : 'Pending Review'),
                'data' => [
                    'tournament_id' => $tournament->id,
                    'registration_id' => $registration->id,
                ],
                'action_url' => route('manager.tournaments.show', $tournament->id),
            ]);
            
            if ($isEligible) {
                return back()->with('success', "Registration successful! Please contact the manager to complete payment. Fee: ₱{$tournament->tournament_fee}");
            } else {
                return back()->with('success', 'Registration submitted successfully! Pending eligibility review.');
            }
            
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
        
        if (!in_array($registration->status, ['approved'])) {
            return back()->with('error', 'You can only withdraw from approved registrations.');
        }
        
        $tournament = $registration->tournament;
        $withdrawalDeadline = Carbon::parse($tournament->start_date)->subDays(3);
        
        if (Carbon::now()->isAfter($withdrawalDeadline)) {
            return back()->with('error', 'Withdrawal deadline has passed. You cannot withdraw within 3 days of the tournament start.');
        }
        
        $registration->update(['status' => 'withdrawn']);
        
        $manager = $tournament->club->manager;
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
}
