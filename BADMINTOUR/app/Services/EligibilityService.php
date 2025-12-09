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
}