<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\TournamentCategory;
use App\Models\TournamentRegistration;
use App\Models\ClubPlayer;
use App\Services\EligibilityService;
use Illuminate\Http\Exceptions\HttpResponseException;

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

        // Require partner for doubles/mixed before proceeding
        $name = strtolower($category->name ?? '');
        $matchType = strtolower($category->match_type ?? '');
        $type = strtolower($category->type ?? '');
        $isDoubles = str_contains($name, 'doubles') || str_contains($name, 'mixed')
            || str_contains($matchType, 'double') || str_contains($matchType, 'mixed')
            || in_array($type, ['md', 'wd', 'xd', 'doubles', 'mixed'], true);
        if ($isDoubles && empty($this->partner_id)) {
            session()->flash('error', 'You must link a confirmed partner before submitting registration for this doubles/mixed category.');
            return false;
        }
        
        $alreadyRegistered = TournamentRegistration::where('player_id', $user->id)
            ->where('category_id', $category->id)
            ->whereIn('status', ['pending', 'eligible', 'approved', 'withdrawn'])
            ->exists();
        
        if ($alreadyRegistered) {
            $existingReg = TournamentRegistration::where('player_id', $user->id)
                ->where('category_id', $category->id)
                ->first();
            if ($existingReg && $existingReg->status === 'withdrawn') {
                session()->flash('error', 'You have withdrawn from this tournament category and cannot re-register.');
                return false;
            }
        }
        
        if ($alreadyRegistered) {
            session()->flash('error', 'You are already registered for this category.');
            return false;
        }
        
        // Check if player has an approved registration for another tournament on the same date
        // Exclude withdrawn registrations - players who withdrew can register for another tournament on the same date
        $tournament = $category->tournament;
        $hasSameDateRegistration = TournamentRegistration::where('player_id', $user->id)
            ->where('status', 'approved') // Only check approved registrations
            ->whereHas('tournament', function($query) use ($tournament) {
                $query->where('start_date', $tournament->start_date)
                      ->where('id', '!=', $tournament->id)
                      ->whereIn('status', ['published', 'upcoming', 'ongoing']); // Only upcoming/ongoing tournaments
            })
            ->exists();
        
        if ($hasSameDateRegistration) {
            session()->flash('error', 'You are already registered for an upcoming tournament on this date.');
            return false;
        }
        
        // For doubles/mixed categories, also check if partner has a same-date conflict
        $partner = $this->partner_id ? \App\Models\User::find($this->partner_id) : null;
        if ($partner) {
            $partnerHasSameDateRegistration = TournamentRegistration::where('player_id', $partner->id)
                ->where('status', 'approved')
                ->whereHas('tournament', function($query) use ($tournament) {
                    $query->where('start_date', $tournament->start_date)
                          ->where('id', '!=', $tournament->id)
                          ->whereIn('status', ['published', 'upcoming', 'ongoing']);
                })
                ->exists();
            
            if ($partnerHasSameDateRegistration) {
                session()->flash('error', 'Your partner is already registered for an upcoming tournament on this date.');
                return false;
            }
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
        
        // Check if partner is already registered with a DIFFERENT player (not the current user)
        // Allow if partner has an accepted invitation with the current user
        if ($partner) {
            // Check if there's an accepted invitation between user and partner
            $acceptedInvitation = \App\Models\PartnerInvitation::where('tournament_id', $tournament->id)
                ->where('category_id', $category->id)
                ->where('status', 'accepted')
                ->where(function($query) use ($user, $partner) {
                    $query->where(function($q) use ($user, $partner) {
                        $q->where('inviter_id', $user->id)
                          ->where('invitee_id', $partner->id);
                    })->orWhere(function($q) use ($user, $partner) {
                        $q->where('inviter_id', $partner->id)
                          ->where('invitee_id', $user->id);
                    });
                })
                ->exists();
            
            // Check if partner is registered with someone else (not the current user)
            $partnerRegisteredWithOther = TournamentRegistration::where('category_id', $category->id)
                ->where(function($query) use ($partner, $user) {
                    // Partner is registered as player with a different partner
                    $query->where(function($subQ) use ($partner, $user) {
                        $subQ->where('player_id', $partner->id)
                             ->whereNotNull('partner_id')
                             ->where('partner_id', '!=', $user->id);
                    })
                    // OR partner is registered as partner with a different player
                    ->orWhere(function($subQ) use ($partner, $user) {
                        $subQ->where('partner_id', $partner->id)
                             ->where('player_id', '!=', $user->id);
                    });
                })
                ->whereIn('status', ['pending', 'eligible', 'approved'])
                ->exists();
            
            // Only error if partner is registered with someone else AND there's no accepted invitation
            if ($partnerRegisteredWithOther && !$acceptedInvitation) {
                session()->flash('error', 'The selected partner is already registered with another player in this category.');
                return false;
            }
            
            // For doubles/mixed, require that partner_id matches an accepted invitation
            $name = strtolower($category->name ?? '');
            $matchType = strtolower($category->match_type ?? '');
            $type = strtolower($category->type ?? '');
            $isDoubles = str_contains($name, 'doubles') || str_contains($name, 'mixed')
                || str_contains($matchType, 'double') || str_contains($matchType, 'mixed')
                || in_array($type, ['md', 'wd', 'xd', 'doubles', 'mixed'], true);
            
            if ($isDoubles && !$acceptedInvitation) {
                session()->flash('error', 'You must have an accepted partner invitation before registering for this doubles/mixed category.');
                return false;
            }
        }
        
        return true;
    }

    /**
     * Prevent 403 responses by redirecting back with the stored error message.
     */
    protected function failedAuthorization()
    {
        $message = session('error') ?? 'You are not allowed to register for this category.';
        throw new HttpResponseException(
            redirect()->back()->with('error', $message)
        );
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
