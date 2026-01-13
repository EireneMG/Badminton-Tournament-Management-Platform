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
        
        // Normalize genders for comparison
        $playerGender = strtolower($player->gender ?? '');
        $partnerGender = $partner ? strtolower($partner->gender ?? '') : null;
        
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
        
        // 4. Check gender requirement (for singles/doubles categories)
        $categoryName = strtolower($category->name ?? '');
        $categoryTypeCode = strtoupper($category->type ?? '');
        $isMixedDoubles = str_contains($categoryName, 'mixed') || $categoryTypeCode === 'XD';
        $isWomenCategory = str_contains($categoryName, 'women') || in_array($categoryTypeCode, ['WS', 'WD'], true);
        // check men only if not already classified as women to avoid "women" matching "men"
        $isMenCategory = !$isWomenCategory && (str_contains($categoryName, "men'") || str_contains($categoryName, 'mens') || str_contains($categoryName, 'men ') || in_array($categoryTypeCode, ['MS', 'MD'], true));
        
        // Singles/doubles flags (used only for messaging/partner checks)
        $isSingles = str_contains($categoryName, 'single') || in_array($categoryTypeCode, ['MS', 'WS'], true);
        $isDoubles = str_contains($categoryName, 'double') || in_array($categoryTypeCode, ['MD', 'WD', 'XD'], true);

        if (!$isMixedDoubles) {
            if ($isWomenCategory && $playerGender !== 'female') {
                $eligible = false;
                $reasons[] = 'Category is for women only.';
            } elseif ($isMenCategory && $playerGender !== 'male') {
                $eligible = false;
                $reasons[] = 'Category is for men only.';
            }
        }
        
        // 5. Check partner eligibility (for doubles/mixed doubles)
        if ($partner) {
            $partnerEligibility = $this->checkEligibility($partner, $category);
            if (!$partnerEligibility['eligible']) {
                $eligible = false;
                $reasons[] = 'Partner is not eligible: ' . implode(' ', $partnerEligibility['reasons']);
            }
            
            if ($isMixedDoubles) {
                if ($playerGender === $partnerGender) {
                    $eligible = false;
                    $reasons[] = 'Mixed doubles requires one male and one female player.';
                }
            } else {
                if ($isWomenCategory && $partnerGender !== 'female') {
                    $eligible = false;
                    $reasons[] = 'Partner must be female for this category.';
                }
                if ($isMenCategory && $partnerGender !== 'male') {
                    $eligible = false;
                    $reasons[] = 'Partner must be male for this category.';
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
        $referenceDate = Carbon::parse($category->tournament->start_date ?? Carbon::now());
        
        // Calculate age accurately: check if birthday has occurred in the reference year
        $age = $referenceDate->year - $birthDate->year;
        if ($referenceDate->month < $birthDate->month || 
            ($referenceDate->month === $birthDate->month && $referenceDate->day < $birthDate->day)) {
            $age--; // Birthday hasn't occurred yet this year
        }
        
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
        
        if ($eligibility['eligible'] && in_array($registration->status, ['pending'])) {
            $registration->update(['status' => 'eligible']);
            return true;
        }
        
        if (!$eligibility['eligible'] && in_array($registration->status, ['eligible'])) {
            $registration->update(['status' => 'pending']);
            return false;
        }
        
        return $eligibility['eligible'];
    }
}

