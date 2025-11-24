<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Club extends Model
{
    protected $fillable = [
        'manager_id',
        'name',
        'description',
        'logo',
        'province',
        'city',
        'contact_email',
        'contact_phone',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function clubPlayers(): HasMany
    {
        return $this->hasMany(ClubPlayer::class);
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'club_players', 'club_id', 'player_id')
            ->withPivot('status', 'skill_level', 'provisional_elo')
            ->withTimestamps();
    }

    public function approvedPlayers(): BelongsToMany
    {
        return $this->players()->wherePivot('status', 'approved');
    }

    public function tournaments(): HasMany
    {
        return $this->hasMany(Tournament::class);
    }

    public function hasMinimumPlayers(): bool
    {
        return $this->approvedPlayers()->count() >= 5;
    }
}
