<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\TournamentCategory;
use App\Models\TournamentRegistration;
use App\Models\ClubPlayer;
use App\Services\EligibilityService;

class RegisterTournamentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * This method uses EligibilityService as the single source of truth for eligibility checks.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        
        if (!$user || $user->role !== 'player') {
            return false;
        }
        
        $category = TournamentCategory::find($this->category_id);
        
        if (!$category) {
            return false;
        }
        
        // Check if already registered
        $alreadyRegistered = TournamentRegistration::where('player_id', $user->id)
            ->where('category_id', $category->id)
            ->whereIn('status', ['pending', 'pending_payment', 'awaiting_payment', 'paid', 'approved'])
            ->exists();
        
        if ($alreadyRegistered) {
            session()->flash('error', 'You are already registered for this category.');
            return false;
        }
        
        // Use EligibilityService as single source of truth for eligibility checks
        $eligibilityService = app(EligibilityService::class);
        
        // Get partner if provided
        $partner = $this->partner_id ? \App\Models\User::find($this->partner_id) : null;
        
        // Check eligibility using EligibilityService
        $eligibility = $eligibilityService->checkEligibility($user, $category, $partner);
        
        if (!$eligibility['eligible']) {
            $errorMessage = 'Registration not allowed: ' . implode(' ', $eligibility['reasons']);
            session()->flash('error', $errorMessage);
            return false;
        }
        
        // Validate interclub-specific rules (if applicable)
        $tournament = $category->tournament;
        if ($tournament->is_dual_meet) {
            if (!$this->validateInterclubRules($user, $category, $this->partner_id)) {
                return false;
            }
        }
        
        // Check if partner is already registered (additional validation)
        if ($partner) {
            $partnerAlreadyRegistered = TournamentRegistration::where('category_id', $category->id)
                ->where(function($query) use ($partner) {
                    $query->where('player_id', $partner->id)
                          ->orWhere('partner_id', $partner->id);
                })
                ->whereIn('status', ['pending', 'pending_payment', 'awaiting_payment', 'paid', 'approved'])
                ->exists();
            
            if ($partnerAlreadyRegistered) {
                session()->flash('error', 'The selected partner is already registered in this category.');
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:tournament_categories,id'],
            'partner_id' => ['nullable', 'exists:users,id'],
        ];
    }

    /**
     * Validate interclub-specific rules (dual meet tournaments)
     * This is separate from eligibility as it's tournament-type specific
     */

    protected function validateInterclubRules($user, $category, $partnerId = null): bool
    {
        $tournament = $category->tournament;
        
        if (!$tournament->is_dual_meet) {
            return true; // Not an interclub tournament
        }
        
        $userClub = ClubPlayer::where('player_id', $user->id)
            ->where('status', 'approved')
            ->first();
        
        if (!$userClub) {
            session()->flash('error', 'You must be a member of a club to participate in interclub tournaments.');
            return false;
        }
        
        // Validate partner is from different club (if partner provided)
        if ($partnerId) {
            $partnerClub = ClubPlayer::where('player_id', $partnerId)
                ->where('status', 'approved')
                ->first();
            
            if (!$partnerClub) {
                session()->flash('error', 'Your partner must be a member of a club to participate in interclub tournaments.');
                return false;
            }
            
            // For interclub: partners should be from different clubs
            if ($userClub->club_id === $partnerClub->club_id) {
                session()->flash('error', 'In interclub tournaments, partners must be from different clubs.');
                return false;
            }
        }
        
        // Additional interclub validation can be added here
        // (e.g., club-level pairing rules, ranking restrictions)
        
        return true;
    }
}
