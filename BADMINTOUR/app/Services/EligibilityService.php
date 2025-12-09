<?php

namespace App\Services;

use App\Models\User;
use App\Models\TournamentCategory;
use App\Models\TournamentRegistration;
use App\Models\ClubPlayer;
use Carbon\Carbon;

class EligibilityService
{
    /**
     * Check if a player is eligible for a category
     * 
     * @param User $player
     * @param TournamentCategory $category
     * @param User|null $partner For doubles/mixed doubles
     * @return array ['eligible' => bool, 'reasons' => array]
     */
    public function checkEligibility(User $player, TournamentCategory $category, ?User $partner = null): array
    {
        $reasons = [];
        $eligible = true;
        
        // 1. Check club membership
        $clubMembership = ClubPlayer::where('player_id', $player->id)
            ->where('status', 'approved')
            ->first();
        
        if (!$clubMembership) {
            $eligible = false;
            $reasons[] = 'Player must be a member of an approved club.';
        }
        
        // 2. Check age requirement
        if (($category->min_age !== null || $category->max_age !== null) && $player->birth_year) {
            if (!$this->checkAgeRequirement($player, $category)) {
                $eligible = false;
                $reasons[] = 'Player does not meet the age requirement for this category.';
            }
        }
        
        // 3. Check skill level requirement
        if ($category->skill_level && $category->skill_level !== 'Open') {
            if (!$this->checkSkillRequirement($player, $category)) {
                $eligible = false;
                $reasons[] = 'Player does not meet the skill level requirement for this category.';
            }
        }
        
        // 4. Check gender requirement (for singles categories)
        $categoryType = strtolower($category->name ?? '');
        $isMenSingles = str_contains($categoryType, "men's singles") || str_contains($categoryType, 'mens singles') || $category->type === 'MS';
        $isWomenSingles = str_contains($categoryType, "women's singles") || str_contains($categoryType, 'womens singles') || $category->type === 'WS';
        $isMenDoubles = str_contains($categoryType, "men's doubles") || str_contains($categoryType, 'mens doubles') || $category->type === 'MD';
        $isWomenDoubles = str_contains($categoryType, "women's doubles") || str_contains($categoryType, 'womens doubles') || $category->type === 'WD';
        $isMixedDoubles = str_contains($categoryType, 'mixed doubles') || $category->type === 'XD';
        
        if ($isMenSingles && $player->gender !== 'male') {
            $eligible = false;
            $reasons[] = 'Category is for men only.';
        }
        
        if ($isWomenSingles && $player->gender !== 'female') {
            $eligible = false;
            $reasons[] = 'Category is for women only.';
        }
        
        if ($isMenDoubles && $player->gender !== 'male') {
            $eligible = false;
            $reasons[] = 'Category is for men only.';
        }
        
        if ($isWomenDoubles && $player->gender !== 'female') {
            $eligible = false;
            $reasons[] = 'Category is for women only.';
        }
        
        // For mixed doubles, any gender is allowed (no gender restriction)
        
        // 5. Check partner eligibility (for doubles/mixed doubles)
        if ($partner) {
            $partnerEligibility = $this->checkEligibility($partner, $category);
            if (!$partnerEligibility['eligible']) {
                $eligible = false;
                $reasons[] = 'Partner is not eligible: ' . implode(' ', $partnerEligibility['reasons']);
            }
            
            // For men's doubles, partner must be male
            if ($isMenDoubles && $partner->gender !== 'male') {
                $eligible = false;
                $reasons[] = 'Partner must be male for men\'s doubles.';
            }
            
            // For women's doubles, partner must be female
            if ($isWomenDoubles && $partner->gender !== 'female') {
                $eligible = false;
                $reasons[] = 'Partner must be female for women\'s doubles.';
            }
            
            // For mixed doubles, partner must be opposite gender
            if ($isMixedDoubles) {
                if ($player->gender === $partner->gender) {
                    $eligible = false;
                    $reasons[] = 'Mixed doubles requires one male and one female player.';
                }
            }
        }
        
        return [
            'eligible' => $eligible,
            'reasons' => $reasons,
        ];
    }
    
    /**
     * Check age requirement
     */
    protected function checkAgeRequirement(User $player, TournamentCategory $category): bool
    {
        // If no age restrictions, allow all
        if ($category->min_age === null && $category->max_age === null) {
            return true;
        }
        
        // Check if player has birth date
        if (!$player->birth_year) {
            return true; // No age data, assume eligible
        }
        
        // Calculate age from birth_year, birth_month, birth_day
        $birthDate = Carbon::create(
            $player->birth_year,
            $player->birth_month ?? 1,
            $player->birth_day ?? 1
        );
        
        // Use tournament start date as reference for age calculation
        $referenceDate = $category->tournament->start_date ?? Carbon::now();
        $age = $birthDate->diffInYears($referenceDate);
        
        // Check min_age
        if ($category->min_age !== null && $age < $category->min_age) {
            return false;
        }
        
        // Check max_age
        if ($category->max_age !== null && $age > $category->max_age) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check skill level requirement
     */
    protected function checkSkillRequirement(User $player, TournamentCategory $category): bool
    {
        if (!$category->skill_level || $category->skill_level === 'Open') {
            return true;
        }
        
        // Get player's skill level from club membership
        $clubMembership = ClubPlayer::where('player_id', $player->id)
            ->where('status', 'approved')
            ->first();
        
        if (!$clubMembership) {
            return false;
        }
        
        // Skill levels: Open, A, B, C, D
        // Open = all levels allowed
        // A = A only
        // B = B and below (B, C, D)
        // C = C and below (C, D)
        // D = D only
        
        $playerSkillLevel = $clubMembership->skill_level ?? 'D';
        $requiredSkillLevel = $category->skill_level;
        
        if ($requiredSkillLevel === 'Open') {
            return true;
        }
        
        $skillHierarchy = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4];
        $playerLevel = $skillHierarchy[$playerSkillLevel] ?? 4;
        $requiredLevel = $skillHierarchy[$requiredSkillLevel] ?? 4;
        
        // For skill-restricted categories, player must be at or below the required level
        // Example: Category B allows B, C, D (but not A)
        return $playerLevel >= $requiredLevel;
    }
    
    /**
     * Automatically check and update eligibility for a registration
     * 
     * @param TournamentRegistration $registration
     * @return bool True if eligible, false otherwise
     */
    public function checkAndUpdateEligibility(TournamentRegistration $registration): bool
    {
        $player = $registration->player;
        $category = $registration->category;
        $partner = $registration->partner;
        
        $eligibility = $this->checkEligibility($player, $category, $partner);
        
        // If eligible and currently pending, update to awaiting_payment
        if ($eligibility['eligible'] && in_array($registration->status, ['pending', 'pending_payment'])) {
            $registration->update(['status' => 'awaiting_payment']);
            return true;
        }
        
        // If not eligible and currently awaiting_payment or eligible, update to pending
        if (!$eligibility['eligible'] && in_array($registration->status, ['awaiting_payment', 'eligible'])) {
            $registration->update(['status' => 'pending']);
            return false;
        }
        
        return $eligibility['eligible'];
    }
}

