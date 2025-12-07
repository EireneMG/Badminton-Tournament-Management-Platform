<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'first_name',
        'middle_name',
        'last_name',
        'contact_number',
        'id_document',
        'verification_status',
        'birth_month',
        'birth_day',
        'birth_year',
        'gender',
        'height',
        'weight',
        'region',
        'province',
        'city',
        'school_status',
        'school_name',
        'badminton_history',
        'years_of_experience',
        'experience_level',
        'competitive_background',
        'profile_photo',
        'player_id_document',
        'id_type',
        'biodata_completed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'badminton_history' => 'array',
            'biodata_completed' => 'boolean',
        ];
    }

    public function isPlayer(): bool
    {
        return $this->role === 'player';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function getDashboardRoute(): string
    {
        if ($this->isManager()) {
            return 'manager.dashboard';
        }
        
        // For players, redirect to profile completion if biodata is not completed
        if ($this->isPlayer() && !$this->biodata_completed) {
            return 'profile.edit';
        }
        
        return 'player.dashboard';
    }

    public function managedClub(): HasOne
    {
        return $this->hasOne(Club::class, 'manager_id');
    }

    public function clubMemberships(): HasMany
    {
        return $this->hasMany(ClubPlayer::class, 'player_id');
    }

    /**
     * Get the player's current approved club membership.
     * Returns the most recent approved membership for quick lookups.
     */
    public function approvedClubMembership(): HasOne
    {
        return $this->hasOne(ClubPlayer::class, 'player_id')
            ->where('status', 'approved')
            ->latestOfMany();
    }

    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'club_players', 'player_id', 'club_id')
            ->withPivot('status', 'skill_level', 'provisional_elo')
            ->withTimestamps();
    }

    public function organizedTournaments(): HasMany
    {
        return $this->hasMany(Tournament::class, 'organizer_id');
    }

    public function tournamentRegistrations(): HasMany
    {
        return $this->hasMany(TournamentRegistration::class, 'player_id');
    }

    public function eloRatings(): HasMany
    {
        return $this->hasMany(EloRating::class, 'player_id');
    }

    public function rankingHistory(): HasMany
    {
        return $this->hasMany(RankingHistory::class, 'player_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function managerIdVerification(): HasOne
    {
        return $this->hasOne(ManagerIdVerification::class, 'manager_id');
    }

    /**
     * Get the player's activity status (Active or Inactive).
     * 
     * A player is considered Active if they have:
     * - Participated in a match within the last 6 months, OR
     * - Registered for a tournament within the last 6 months, OR
     * - Account created within the last 6 months (new players)
     * 
     * Otherwise, the player is Inactive.
     * 
     * @return string 'Active' or 'Inactive'
     */
    public function getStatus(): string
    {
        if (!$this->isPlayer()) {
            return 'N/A';
        }

        $sixMonthsAgo = now()->subMonths(6);
        
        // Get last match date (completed matches only)
        $lastMatchDate = \App\Models\TournamentMatch::where(function($query) {
                $query->where('player1_id', $this->id)
                      ->orWhere('player2_id', $this->id)
                      ->orWhere('player1_partner_id', $this->id)
                      ->orWhere('player2_partner_id', $this->id);
            })
            ->where('status', 'completed')
            ->max('updated_at');
        
        // Get last tournament registration date
        $lastRegistrationDate = $this->tournamentRegistrations()
            ->max('created_at');
        
        // Get account creation date
        $accountCreationDate = $this->created_at;
        
        // Find the most recent activity date
        $lastActivityDate = collect([
            $lastMatchDate,
            $lastRegistrationDate,
            $accountCreationDate
        ])->filter()->max();
        
        // If no activity date found, use account creation
        if (!$lastActivityDate) {
            $lastActivityDate = $accountCreationDate;
        }
        
        // Convert to Carbon if it's a string
        if (is_string($lastActivityDate)) {
            $lastActivityDate = \Carbon\Carbon::parse($lastActivityDate);
        }
        
        // Check if last activity is within 6 months
        return $lastActivityDate->greaterThanOrEqualTo($sixMonthsAgo) ? 'Active' : 'Inactive';
    }

    /**
     * Check if the player is currently active.
     * 
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->getStatus() === 'Active';
    }
}
