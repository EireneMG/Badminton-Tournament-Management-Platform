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
        return $this->isManager() ? 'manager.dashboard' : 'player.dashboard';
    }

    public function managedClub(): HasOne
    {
        return $this->hasOne(Club::class, 'manager_id');
    }

    public function clubMemberships(): HasMany
    {
        return $this->hasMany(ClubPlayer::class, 'player_id');
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
}
