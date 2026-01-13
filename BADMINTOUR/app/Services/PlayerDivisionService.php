<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class PlayerDivisionService
{
    /**
     * Get player's division based on age
     * 
     * @param User $player
     * @param Carbon|null $referenceDate Reference date for age calculation (defaults to today)
     * @return string 'Junior' or 'Senior'
     */
    public function getPlayerDivision(User $player, ?Carbon $referenceDate = null): ?string
    {
        if (!$player->birth_year) {
            return null; // Cannot determine division without birth date
        }
        
        $referenceDate = $referenceDate ?? Carbon::now();
        
        // Calculate age from birth_year, birth_month, birth_day
        $birthDate = Carbon::create(
            $player->birth_year,
            $player->birth_month ?? 1,
            $player->birth_day ?? 1
        );
        
        // Calculate age accurately: check if birthday has occurred in the reference year
        $age = $referenceDate->year - $birthDate->year;
        if ($referenceDate->month < $birthDate->month || 
            ($referenceDate->month === $birthDate->month && $referenceDate->day < $birthDate->day)) {
            $age--; // Birthday hasn't occurred yet this year
        }
        
        // Junior: Under 18 years old
        // Senior: 18 years old and above
        return $age < 18 ? 'Junior' : 'Senior';
    }
    
    /**
     * Check if player belongs to a specific division
     * 
     * @param User $player
     * @param string $division 'Junior', 'Senior', or 'Open'
     * @param Carbon|null $referenceDate
     * @return bool
     */
    public function isInDivision(User $player, string $division, ?Carbon $referenceDate = null): bool
    {
        if ($division === 'Open' || $division === 'Open (All Ages)') {
            return true; // Open division includes all players
        }
        
        $playerDivision = $this->getPlayerDivision($player, $referenceDate);
        
        if (!$playerDivision) {
            return false; // Cannot determine, assume not eligible
        }
        
        return $playerDivision === $division;
    }
    
    /**
     * Get all available divisions
     * 
     * @return array
     */
    public function getAvailableDivisions(): array
    {
        return [
            'Junior' => 'Junior (Under 18)',
            'Senior' => 'Senior (18+)',
            'Open' => 'Open (All Ages)',
        ];
    }
    
    /**
     * Parse age bracket string to division
     * Handles legacy formats and new formats
     * 
     * @param string|null $ageBracket
     * @return string|null
     */
    public function parseAgeBracketToDivision(?string $ageBracket): ?string
    {
        if (!$ageBracket) {
            return 'Open';
        }
        
        // New format: "Junior (Under 18)", "Senior (18+)", "Open (All Ages)"
        if (str_contains($ageBracket, 'Junior')) {
            return 'Junior';
        }
        
        if (str_contains($ageBracket, 'Senior')) {
            return 'Senior';
        }
        
        if (str_contains($ageBracket, 'Open')) {
            return 'Open';
        }
        
        // Legacy format: "Junior (13-16)", "Senior (17-35)", etc.
        // Convert to new format
        if (str_contains($ageBracket, 'Junior')) {
            return 'Junior';
        }
        
        if (str_contains($ageBracket, 'Senior') && !str_contains($ageBracket, 'Veteran') && !str_contains($ageBracket, 'Master')) {
            return 'Senior';
        }
        
        // Default to Open for any unrecognized format
        return 'Open';
    }
}

